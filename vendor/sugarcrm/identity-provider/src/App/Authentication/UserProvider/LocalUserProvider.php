<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\IdentityProvider\App\Authentication\UserProvider;

use Sugarcrm\IdentityProvider\App\Repository\UserRepository;

use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Authentication\UserProvider\LocalUserProvider as BaseLocalUserProvider;
use Sugarcrm\IdentityProvider\STS\Claims;
use Sugarcrm\IdentityProvider\Authentication\Audit;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Ramsey\Uuid\Uuid;

/**
 * Class UserProvider.
 * Class to load user entity from local database.
 */
class LocalUserProvider extends BaseLocalUserProvider
{
    const PROVIDER = Providers::LOCAL;

    /**
     * User attributes which can't be empty on creation
     */
    const REQUIRED_ATTRIBUTES = ['family_name', 'email'];

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var string
     */
    private $tenantId;

    /**
     * @var string
     */
    private $applicationSRN;

    /**
     * @var Audit
     */
    private $audit;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * UserProvider constructor.
     *
     * @param Connection $db
     * @param string $tenantId
     * @param string $applicationSRN
     * @param Audit $audit
     */
    public function __construct(Connection $db, string $tenantId, string $applicationSRN, Audit $audit, UserRepository $userRepository)
    {
        $this->db = $db;
        $this->tenantId = $tenantId;
        $this->applicationSRN = $applicationSRN;
        $this->audit = $audit;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->loadUserByFieldAndProvider($username, self::PROVIDER);
    }

    /**
     * Find and load User by identity-value and provider code.
     *
     * @param string $value identity-value to to search User against
     * @param string $provider code of the provider user originates from
     * @return User
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByFieldAndProvider($value, $provider)
    {
        return $this->userRepository->loadUserByFieldAndProvider($this->tenantId, $value, $provider);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!($user instanceof User)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $userData = $this->userRepository->getUserData($this->tenantId, $user->getUsername(), self::PROVIDER, User::STATUS_ACTIVE);

        return new User($userData['identity_value'], $userData['password_hash'], $userData);
    }

    /**
     * Create User inside local database.
     *
     * @param string $value identity-value for a User in provider
     * @param string $provider code of the provider user originates from
     * @param array $data all additional User attributes
     * @return User
     *
     * @throws DBALException if SQL-insert was incorrect.
     * @throws \Throwable
     */
    public function createUser($value, $provider, $data = [])
    {
        $newUserId = (string)Uuid::uuid4();
        $oidcAttributesKeys = array_flip(Claims::OIDC_ATTRIBUTES);

        // we need a valid email
        $emailValue = filter_var($value, FILTER_VALIDATE_EMAIL)
            ? $value
            : sprintf('%s@%s.com', str_replace('@', '_at_', $value), $this->tenantId);
        $attributes = array_merge(
            // required attributes
            array_combine(
                self::REQUIRED_ATTRIBUTES,
                array_fill(0, count(self::REQUIRED_ATTRIBUTES), $emailValue)
            ),
            // existing attributes
            array_intersect_key($data, $oidcAttributesKeys)
        );

        $userData = [
            'id' => $newUserId,
            'create_time' => gmdate("Y-m-d H:i:s"),
            'modify_time' => gmdate("Y-m-d H:i:s"),
            'created_by' => $this->applicationSRN,
            'modified_by' => $this->applicationSRN,
            'status' => (string)User::STATUS_ACTIVE,
            'tenant_id' => $this->tenantId,
            'user_type' => (string)User::USER_TYPE_REGULAR_USER,
            'attributes' => json_encode($attributes),
            'custom_attributes' => json_encode(array_diff_key($data, $oidcAttributesKeys)),
        ];
        try {
            $this->db->transactional(function ($connection) use ($userData, $newUserId, $provider, $value) {
                $connection->insert('users', $userData);
                $this->linkUserExecutor($connection, $newUserId, $provider, $value);
            });
        } catch (DBALException $e) {
            throw $e;
        }

        // we need raw data in User
        $userData['attributes'] = json_decode($userData['attributes'], true);
        $userData['custom_attributes'] = json_decode($userData['custom_attributes'], true);

        $this->audit->audit('Create User', $newUserId, [], $userData);
        return new User($value, null, $userData);
    }

    /**
     * Link user to provider.
     * @param string $userId
     * @param string $provider
     * @param string $identityValue
     * @throws \Throwable
     */
    public function linkUser(string $userId, string $provider, string $identityValue): void
    {
        $this->linkUserExecutor($this->db, $userId, $provider, $identityValue);
    }

    /**
     * @param Connection $connection
     * @param string $userId
     * @param string $provider
     * @param string $identityValue
     * @throws DBALException
     */
    private function linkUserExecutor(
        Connection $connection,
        string $userId,
        string $provider,
        string $identityValue
    ): void {
        $userProviderData = [
            'tenant_id' => $this->tenantId,
            'user_id' => $userId,
            'provider_code' => $provider,
            'identity_value' => $identityValue,
        ];
        $connection->insert('user_providers', $userProviderData);
    }


    /**
     * Check is exists deactivated user
     *
     * @param string $value identity-value to to search User against
     * @param string $providerCode code of the provider user originates from
     * @return bool
     */
    public function isDeactivatedUserExist(string $value, string $providerCode): bool
    {
        $row = $this->userRepository->getUserData($this->tenantId, $value, $providerCode, User::STATUS_INACTIVE);
        return (bool)$row;
    }

    /**
     * Update User attributes
     *
     * @param array $data
     * @param User $user
     * @throws DBALException if SQL-insert was incorrect.
     */
    public function updateUserAttributes(array $data, User $user): void
    {
        $oldUserData = [
            'attributes' => (array)$user->getAttribute('attributes'),
            'custom_attributes' => (array)$user->getAttribute('custom_attributes'),
        ];

        $oidcAttributesKeys = array_flip(Claims::OIDC_ATTRIBUTES);
        $updateAttributes = [
            'modify_time' => $this->getCurrentDate(),
            'modified_by' => $this->applicationSRN,
            'attributes' => array_intersect_key($data, $oidcAttributesKeys),
            'custom_attributes' => array_diff_key($data, $oidcAttributesKeys),
        ];
        $this->db->update(
            'users',
            [
                'modify_time' => $updateAttributes['modify_time'],
                'modified_by' => $updateAttributes['modified_by'],
                'attributes' => json_encode($updateAttributes['attributes'], JSON_FORCE_OBJECT),
                'custom_attributes' => json_encode($updateAttributes['custom_attributes'], JSON_FORCE_OBJECT),
            ],
            [
                'tenant_id' => $this->tenantId,
                'id' => $user->getAttribute('id'),
            ]
        );
        // Update User object to be consistent with DB changes.
        foreach ($updateAttributes as $attribute => $value) {
            $user->setAttribute($attribute, $value);
        }

        $this->audit->audit('Update User', $user->getAttribute('id'), $oldUserData, $updateAttributes);
    }

    /**
     * Get current date.
     *
     * @return string
     */
    public function getCurrentDate(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}
