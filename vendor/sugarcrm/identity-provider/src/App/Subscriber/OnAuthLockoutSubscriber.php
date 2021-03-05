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

namespace Sugarcrm\IdentityProvider\App\Subscriber;

use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\App\Authentication\Lockout;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\Srn\Converter;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Sugarcrm\IdentityProvider\Authentication\Exception\PermanentLockedUserException;
use Sugarcrm\IdentityProvider\Authentication\Exception\TemporaryLockedUserException;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class OnAuthLockoutSubscriber implements EventSubscriberInterface
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var Lockout
     */
    protected $lockout;

    /**
     * @param Application $app
     * @param Lockout $lockout
     */
    public function __construct(Application $app, Lockout $lockout)
    {
        $this->app = $app;
        $this->lockout = $lockout;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onSuccess',
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onFailure',
        ];
    }

    /**
     * runs on success
     * @param AuthenticationEvent $event
     */
    public function onSuccess(AuthenticationEvent $event)
    {
        if (!$this->lockout->isEnabled()) {
            return;
        }

        /** @var TokenInterface $token */
        $token = $event->getAuthenticationToken();
        if (!$this->supports($token)) {
            return;
        }

        /** @var User $user */
        $user = $token->getUser();
        $userId = $user->getAttribute('id');
        if ($user->getAttribute('failed_login_attempts') || $user->getAttribute('is_locked_out')) {
            $this->app->getDoctrineService()->executeUpdate(
                'UPDATE users SET is_locked_out = false, failed_login_attempts = 0 WHERE id = ? AND tenant_id = ?',
                [$userId, $user->getAttribute('tenant_id')]
            );
            $user->setAttribute('is_locked_out', false);
            $user->setAttribute('failed_login_attempts', 0);
        }

        return;
    }

    /**
     * runs on failure
     * @param AuthenticationFailureEvent $event
     * @throws \Doctrine\DBAL\DBALException
     */
    public function onFailure(AuthenticationFailureEvent $event): void
    {
        $isPermanentFail = $event->getAuthenticationException() instanceof PermanentLockedUserException;
        $isTemporaryFail = $event->getAuthenticationException() instanceof TemporaryLockedUserException;
        if ($isPermanentFail || $isTemporaryFail) {
            return;
        }

        /** @var TokenInterface $token */
        $token = $event->getAuthenticationToken();
        if (!$this->supports($token)) {
            return;
        }

        $username = $token->getUsername();
        /** @var User $user */
        try {
            $user = $this->getLocalUserProvider()->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            $user = null;
        }
        if ($user) {
            $this->app->getDoctrineService()->executeUpdate(
                'UPDATE users SET is_locked_out = false, ' .
                'failed_login_attempts = failed_login_attempts + 1 WHERE id = ? AND tenant_id = ?',
                [$user->getAttribute('id'), $user->getAttribute('tenant_id')]
            );
            $user->setAttribute('is_locked_out', false);
            $user->setAttribute('failed_login_attempts', (int) $user->getAttribute('failed_login_attempts') + 1);

            $this->app->getLogger()->info(
                "FAILED LOGIN:attempts['{failed_login_attempts}'] - for user {user_name} with SRN {user_srn}",
                [
                    'failed_login_attempts' => $user->getAttribute('failed_login_attempts'),
                    'user_name' => $username,
                    'user_srn' => $token->hasAttribute('srn') ? $token->getAttribute('srn') : 'unknown',
                ]
            );
        } else {
            $this->app->getLogger()->info('FAILED LOGIN: for user {user_name} with SRN {user_srn}', [
                'user_name' => $username,
                'user_srn' => $token->hasAttribute('srn') ? $token->getAttribute('srn') : 'unknown',
            ]);
        }

        if (!$this->lockout->isEnabled() || !$user) {
            return;
        }

        if ((int) $user->getAttribute('failed_login_attempts') >= $this->lockout->getAllowedFailedLoginCount()) {
            $lockoutTime = (new \DateTime())->format('Y-m-d H:i:s');
            $this->app->getDoctrineService()->executeUpdate(
                'UPDATE users SET is_locked_out = true, failed_login_attempts = 0, lockout_time = ? '
                    . 'WHERE id = ? AND tenant_id = ?',
                [$lockoutTime, $user->getAttribute('id'), $user->getAttribute('tenant_id')]
            );
            $user->setAttribute('is_locked_out', true);
            $user->setAttribute('lockout_time', $lockoutTime);
            $user->setAttribute('failed_login_attempts', 0);
        }
    }

    /**
     * Get LocalUserProvider.
     * We load it lazily to initialize it with tenant-id. Otherwise it'd be better to inject it into constructor.
     *
     * @return UserProviderInterface
     */
    protected function getLocalUserProvider(): UserProviderInterface
    {
        $tenant = $this->session->get(TenantConfigInitializer::SESSION_KEY);
        $tenantSrn = Converter::fromString($tenant);
        return $this->app->getUserProviderBuilder()->build($tenantSrn, Providers::PROVIDER_KEY_LOCAL);
    }

    /**
     * Is token supported?
     * @param TokenInterface $token
     * @return bool
     */
    protected function supports(TokenInterface $token)
    {
        return $token instanceof UsernamePasswordToken
            && $token->getProviderKey() === Providers::PROVIDER_KEY_LOCAL;
    }
}
