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

use Sugarcrm\IdentityProvider\App\Application;

use Sugarcrm\IdentityProvider\Authentication\Audit;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Srn\Converter;
use Sugarcrm\IdentityProvider\Srn\Srn;

use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Builds user providers
 */
class UserProviderBuilder
{
    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get user provider
     * @param Srn $tenantSrn Tenant SRN object
     * @param string $providerKey Provider type
     * @return UserProviderInterface|null
     */
    public function build(Srn $tenantSrn, string $providerKey):? UserProviderInterface
    {
        switch ($providerKey) {
            case Providers::PROVIDER_KEY_LOCAL:
                return new LocalUserProvider(
                    $this->app->getDoctrineService(),
                    $tenantSrn->getTenantId(),
                    $this->app->getOAuth2Service()->getClientID(),
                    $this->getAudit(Converter::toString($tenantSrn)),
                    $this->app->getUserRepository()
                );
            break;
            case Providers::PROVIDER_KEY_LDAP:
                $appConfig = $this->app->getTenantConfiguration()->merge($tenantSrn, $this->app['config']);
                if (empty($appConfig['ldap'])) {
                    return null;
                }

                $config = $appConfig['ldap'];

                $adapter = new Adapter($config['adapter_config']);
                if (!empty($config['adapter_connection_protocol_version'])) {
                    $adapter->getConnection()->setOption('PROTOCOL_VERSION', $config['adapter_connection_protocol_version']);
                }

                $ldap = new Ldap($adapter);

                return new LdapUserProvider(
                    $ldap,
                    $config['baseDn'],
                    $config['searchDn'],
                    $config['searchPassword'],
                    User::getDefaultRoles(),
                    $config['uidKey'],
                    $config['filter'],
                    null,
                    $this->app->getUserRepository()
                );
            break;
            case Providers::PROVIDER_KEY_SAML:
                return new SAMLUserProvider($this->app->getUserRepository());
            break;
            case Providers::PROVIDER_KEY_OIDC:
                return new OIDCUserProvider($this->app->getUserRepository());
            break;
            default:
                return null;
        }
    }

    /**
     * @param Application $app
     * @return Audit
     */
    private function getAudit(string $tenantSrn): Audit
    {
        return new Audit(
            $this->app->getLogger(),
            $tenantSrn,
            $this->app->getOAuth2Service()->getClientID()
        );
    }
}
