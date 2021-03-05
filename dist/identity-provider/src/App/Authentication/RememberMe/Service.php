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

namespace Sugarcrm\IdentityProvider\App\Authentication\RememberMe;

use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\UserProviderBuilder;

use Sugarcrm\IdentityProvider\Authentication\Audit;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeTokenInterface;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Srn;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Doctrine\DBAL\Connection;

class Service
{
    /**
     * @var SessionInterface
     */
    protected $storage;

    const STORAGE_KEY = 'loggedInIdentities';

    public const MAX_IDENTITIES = 10;

    /**
     * @var UserProviderBuilder
     */
    private $userProviderBuilder;

    /**
     * Service constructor.
     * @param SessionInterface $session
     * @param Connection $db
     * @param string $applicationSRN
     * @param Audit $audit
     */
    public function __construct(
        SessionInterface $session,
        UserProviderBuilder $userProviderBuilder
    ) {
        $this->storage = $session;
        $this->userProviderBuilder = $userProviderBuilder;

        $this->handleOldTokens();
    }

    /**
     * Stores the token
     *
     * @param RememberMeTokenInterface $rememberMeToken
     */
    public function store(RememberMeTokenInterface $rememberMeToken): void
    {
        $storedToken = $this->retrieveBySrn($rememberMeToken->getSRN());
        if ($storedToken) {
            $this->remove($storedToken);
        }
        $rememberMeToken->setLoggedActive();
        $currentTokens = $this->list();
        array_walk(
            $currentTokens,
            static function (&$token) {
                $token->setLoggedInactive();
            }
        );
        array_unshift($currentTokens, $rememberMeToken);
        $this->storage->set(self::STORAGE_KEY, array_slice($currentTokens, 0, static::MAX_IDENTITIES));
    }
    /**
     * Retrieves remembered active token
     *
     * @return TokenInterface|null
     */
    public function retrieve(): ?RememberMeToken
    {
        $currentTokens = $this->list();

        foreach ($currentTokens as $token) {
            if ($token->isActive()) {
                if ($this->isLocalUserActive($token->getSource())) {
                    return $token;
                } else {
                    $this->remove($token);
                }
            }
        }
        return null;
    }

    /**
     * Get remembered token by user SRN
     * @param string $srn
     * @return RememberMeToken|null
     */
    public function retrieveBySrn(string $srn): ?RememberMeToken
    {
        $currentTokens = $this->list();

        foreach ($currentTokens as $token) {
            if ($token->getSrn() === $srn) {
                return $token;
            }
        }

        return null;
    }

    /**
     * Get all tokens in session
     * @return array
     */
    public function list(): array
    {
        return $this->storage->get(self::STORAGE_KEY) ?? [];
    }

    /**
     * Convert all old stored tokens into new format
     */
    private function handleOldTokens(): void
    {
        $storedTokens = $this->list();
        $hasActiveToken = false;
        $hasOldToken = false;
        foreach ($storedTokens as $key => $token) {
            if (!$token instanceof RememberMeTokenInterface) {
                $storedTokens[$key] = new RememberMeToken($token);
                $hasOldToken = true;
            } elseif (!$hasActiveToken) {
                $hasActiveToken = $token->isActive();
            }
        }

        if (!$hasActiveToken && $hasOldToken) {
            /** @var RememberMeTokenInterface $token */
            $token = $storedTokens[0];
            $token->setLoggedActive();
        }

        if ($hasOldToken) {
            $this->storage->set(self::STORAGE_KEY, $storedTokens);
        }
    }

    /**
     * Clear remembered tokens
     */
    public function clear(): void
    {
        $this->storage->remove(self::STORAGE_KEY);
    }

    /**
     * Remove remembered token from list
     * @param TokenInterface $token
     */
    public function remove(TokenInterface $token): void
    {
        $currentTokens = $this->list();

        foreach ($currentTokens as $key => $value) {
            if ($token === $value) {
                unset($currentTokens[$key]);
                $this->storage->set(self::STORAGE_KEY, $currentTokens);
            }
        }
    }

    /**
     * Refresh user data in stored tokens
     */
    public function refreshUserData(): void
    {
        foreach ($this->list() as $token) {
            $tenantSrn = Srn\Converter::fromString($token->getAttribute('tenantSrn'));
            $userProvider = $this->userProviderBuilder->build($tenantSrn, $token->getProviderKey());
            $user = $token->getUser();
            $user->setSrn($token->getAttribute('srn'));
            $token->setUser($userProvider->refreshUser($user));
        }
    }

    /**
     * Check if stored user is active and exists
     * @param TokenInterface $token
     * @return bool
     */
    protected function isLocalUserActive(TokenInterface $token): bool
    {
        if (!$token instanceof UsernamePasswordToken
            || $token->getProviderKey() != Providers::PROVIDER_KEY_LOCAL) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return true;
        }

        if (!$token->hasAttribute('tenantSrn')) {
            return true;
        }

        $tenantSrn = Srn\Converter::fromString($token->getAttribute('tenantSrn'));
        $localProvider = $this->userProviderBuilder->build($tenantSrn, Providers::PROVIDER_KEY_LOCAL);
        try {
            $localProvider->loadUserByFieldAndProvider($user->getUsername(), Providers::LOCAL);
        } catch (UsernameNotFoundException $e) {
            return false;
        }

        return true;
    }
}
