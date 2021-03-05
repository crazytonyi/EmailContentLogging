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

namespace Sugarcrm\IdentityProvider\App\Authentication\User;

use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\LocalUserProvider;

use Sugarcrm\IdentityProvider\Authentication\Exception\InvalidIdentifier\EmptyFieldException;
use Sugarcrm\IdentityProvider\Authentication\Exception\InvalidIdentifier\EmptyIdentifierException;
use Sugarcrm\IdentityProvider\Authentication\Exception\InvalidIdentifier\IdentifierInvalidFormatException;
use Sugarcrm\IdentityProvider\Authentication\User as LocalUser;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;

use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * This class performs post authentication checking for OIDC user.
 *
 * @package Sugarcrm\IdentityProvider\Authentication\User
 */
class OIDCUserChecker extends UserChecker
{
    /**
     * @var LocalUserProvider
     */
    protected $localUserProvider;

    /**
     * OIDC provider configuration.
     * @var array
     */
    protected $config;

    public function __construct(LocalUserProvider $localUserProvider, array $config)
    {
        $this->localUserProvider = $localUserProvider;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function checkPostAuth(UserInterface $user): void
    {
        $value = $user->getAttribute('identityValue');
        $field = $user->getAttribute('identityField');
        $this->validateIdentifier($field, $value);

        try {
            $localUser = $this->getLocalUser($value);
        } catch (UsernameNotFoundException $e) {
            if (empty($this->config['provisionUser'])) {
                throw $e;
            }
            if ($this->localUserProvider->isDeactivatedUserExist($value, Providers::OIDC)) {
                throw new DisabledException('User account is disabled');
            }

            $localUser = $this->localUserProvider->createUser(
                $value,
                Providers::OIDC,
                $user->getAttribute('attributes')
            );
        }
        $user->setLocalUser($localUser);

        parent::checkPostAuth($user);
    }

    /**
     * Validation Identifier
     *
     * @param string $field
     * @param string $nameIdentifier
     * @throws EmptyFieldException
     * @throws EmptyIdentifierException
     * @throws IdentifierInvalidFormatException
     */
    protected function validateIdentifier($field, $nameIdentifier)
    {
        if ('' === $field) {
            throw new EmptyFieldException('Empty field name of identifier');
        }
        if ('' === $nameIdentifier) {
            throw new EmptyIdentifierException('Empty identifier');
        }
    }

    /**
     * @param $value
     * @return LocalUser
     * @throws \Throwable
     */
    private function getLocalUser($value): LocalUser
    {
        try {
            $localUser = $this->localUserProvider->loadUserByFieldAndProvider($value, Providers::OIDC);
        } catch (UsernameNotFoundException $e) {
            $localUser = $this->localUserProvider->loadUserByFieldAndProvider($value, Providers::LOCAL);
            $this->localUserProvider->linkUser(
                $localUser->getAttribute('id'),
                Providers::OIDC,
                $value
            );
        }

        return $localUser;
    }
}
