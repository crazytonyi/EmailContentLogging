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

use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Authentication\UserProvider\SAMLUserProvider as BaseSAMLUserProvider;
use Sugarcrm\IdentityProvider\Srn\Converter;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class SAMLUserProvider extends BaseSAMLUserProvider
{
    const PROVIDER = Providers::SAML;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!($user instanceof User)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $tenantId = Converter::fromString($user->getSrn())->getTenantId();
        $userData = $this->userRepository->getUserData($tenantId, $user->getUsername(), self::PROVIDER, User::STATUS_ACTIVE);

        return new User($userData['identity_value'], $userData['password_hash'], $userData);
    }
}
