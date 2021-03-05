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

namespace Sugarcrm\IdentityProvider\App\Authentication;

use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Authentication\User\LDAPUserChecker;
use Sugarcrm\IdentityProvider\App\Authentication\User\LocalUserChecker;
use Sugarcrm\IdentityProvider\App\Authentication\User\SAMLUserChecker;
use Sugarcrm\IdentityProvider\App\Authentication\User\OIDCUserChecker;
use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\LocalUserProvider;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;

use Sugarcrm\IdentityProvider\Authentication\Provider\LdapAuthenticationProvider;
use Sugarcrm\IdentityProvider\Authentication\Provider\OIDCAuthenticationProvider;
use Sugarcrm\IdentityProvider\Authentication\Provider\SAMLAuthenticationProvider;
use Sugarcrm\IdentityProvider\Authentication\Provider\MixedAuthenticationProvider;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\Audit;
use Sugarcrm\IdentityProvider\Srn\Converter;
use Sugarcrm\IdentityProvider\Srn\Srn;

use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;

use Jose\Loader;

class AuthProviderManagerBuilder
{
    /**
     * tenant srn object
     * @var Srn
     */
    protected $tenant;

    /**
     * @param Application $app Silex application instance.
     * @throws \RuntimeException
     * @return AuthenticationProviderManager
     */
    public function buildAuthProviderManager(Application $app)
    {
        $this->tenant = Converter::fromString($app->getSession()->get(TenantConfigInitializer::SESSION_KEY));

        // todo this is an example. need update manager to make it more flexible ane configurable
        $providers = [
            $this->getLdapAuthProvider($app),
            $this->getLocalAuthProvider($app),
            $this->getSamlAuthIDP($app),
            $this->getOIDCProvider($app),
        ];
        // remove not configured items
        $providers = array_filter($providers);
        if (count($providers) > 1) {
            $providers[] = new MixedAuthenticationProvider($providers, Providers::PROVIDER_KEY_MIXED);
        }

        $authManager = new AuthenticationProviderManager($providers);

        return $authManager;
    }

    /**
     * @param Application $app Silex application instance.
     * @return DaoAuthenticationProvider
     */
    protected function getLocalAuthProvider(Application $app): DaoAuthenticationProvider
    {
        $userProvider = $app->getUserProviderBuilder()->build($this->tenant, Providers::PROVIDER_KEY_LOCAL);
        $userChecker = new LocalUserChecker(new Lockout($app));

        // local auth provider
        $authProvider = new DaoAuthenticationProvider(
            $userProvider,
            $userChecker,
            Providers::PROVIDER_KEY_LOCAL,
            $app->getEncoderFactory()
        );

        return $authProvider;
    }

    /**
     * @param Application $app Silex application instance.
     * @return LdapAuthenticationProvider|null
     */
    protected function getLdapAuthProvider(Application $app)
    {
        if (empty($app['config']['ldap'])) {
            return null;
        }

        $config = $app['config']['ldap'];

        $adapter = new Adapter($config['adapter_config']);
        if (!empty($config['adapter_connection_protocol_version'])) {
            $adapter->getConnection()->setOption('PROTOCOL_VERSION', $config['adapter_connection_protocol_version']);
        }

        $ldap = new Ldap($adapter);

        $userProvider = $app->getUserProviderBuilder()->build($this->tenant, Providers::PROVIDER_KEY_LDAP);
        if (is_null($userProvider)) {
            return null;
        }

        $authProvider = new LdapAuthenticationProvider(
            $userProvider,
            new LDAPUserChecker($this->getLocalUserProvider($app), $config),
            Providers::PROVIDER_KEY_LDAP,
            $ldap,
            $app->getUserMappingService('ldap'),
            $config['dnString'],
            true,
            $config
        );

        return $authProvider;
    }

    /**
     * @param Application $app
     * @return OIDCAuthenticationProvider|null
     */
    protected function getOIDCProvider(Application $app): ?OIDCAuthenticationProvider
    {
        if (empty($app['config']['oidc'])) {
            return null;
        }

        $userProvider = $app->getUserProviderBuilder()->build($this->tenant, Providers::PROVIDER_KEY_OIDC);

        return new OIDCAuthenticationProvider(
            $app['config']['oidc'],
            $userProvider,
            $app->getUserMappingService('oidc'),
            new OIDCUserChecker($this->getLocalUserProvider($app), $app['config']['oidc']),
            $app->getOIDCExternalService(),
            new Loader()
        );
    }

    /**
     * @param Application $app Silex application instance.
     * @return SAMLAuthenticationProvider|null
     */
    protected function getSamlAuthIDP($app)
    {
        if (empty($app['config']['saml'])) {
            return null;
        }

        $config = $app['config']['saml'];

        $userProvider = $app->getUserProviderBuilder()->build($this->tenant, Providers::PROVIDER_KEY_SAML);

        return new SAMLAuthenticationProvider(
            $config,
            $userProvider,
            new SAMLUserChecker($this->getLocalUserProvider($app), $config),
            $app->getSession(),
            $app->getUserMappingService('saml')
        );
    }

    /**
     * @param Application $app Silex application instance.
     * @return LocalUserProvider
     */
    protected function getLocalUserProvider($app)
    {
        return $app->getUserProviderBuilder()->build($this->tenant, Providers::PROVIDER_KEY_LOCAL);
    }

    /**
     * @param Application $app
     * @return Audit
     */
    private function getAudit(Application $app): Audit
    {
        return new Audit(
            $app->getLogger(),
            Converter::toString($this->tenant),
            $app->getOAuth2Service()->getClientID()
        );
    }
}
