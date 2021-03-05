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

namespace Sugarcrm\IdentityProvider\App\Listener\Success;

use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\LocalUserProvider;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\Srn\Converter;

use Sugarcrm\IdentityProvider\App\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class UpdateUserAttributesListener
{
    /**
     * @var Application
     */
    private $app;

    /**
     * UpdateUserAttributesListener constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Updates user attributes if they have changed.
     *
     * @param AuthenticationEvent $event
     * @param string $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function __invoke(AuthenticationEvent $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        /** @var TokenInterface $token */
        $token = $event->getAuthenticationToken();
        if ($token instanceof UsernamePasswordToken
            && Providers::PROVIDER_KEY_LOCAL === $token->getProviderKey()) {
            return;
        }
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        $localUser = $user->getLocalUser();
        $localAttr = array_merge(
            (array)$localUser->getAttribute('attributes'),
            (array)$localUser->getAttribute('custom_attributes')
        );
        $oldAttr = array_intersect_key($localAttr, $user->getAttribute('attributes'));
        if (!array_diff($user->getAttribute('attributes'), $oldAttr)) {
            return;
        }

        $this->getLocalUserProvider()->updateUserAttributes(
            array_merge($localAttr, $user->getAttribute('attributes')),
            $localUser
        );
    }

    /**
     * Get LocalUserProvider.
     * We load it lazily to initialize it with tenant-id. Otherwise it'd be better to inject it into constructor.
     *
     * @return LocalUserProvider
     */
    protected function getLocalUserProvider(): LocalUserProvider
    {
        $tenant = $this->app->getSession()->get(TenantConfigInitializer::SESSION_KEY);
        $tenantSrn = Converter::fromString($tenant);
        return $this->app->getUserProviderBuilder()->build($tenantSrn, Providers::PROVIDER_KEY_LOCAL);
    }
}
