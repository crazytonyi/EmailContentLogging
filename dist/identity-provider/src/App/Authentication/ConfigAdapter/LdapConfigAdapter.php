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

namespace Sugarcrm\IdentityProvider\App\Authentication\ConfigAdapter;

class LdapConfigAdapter extends AbstractConfigAdapter
{
    const NETWORK_TIMEOUT = 20; // NETWORK_TIMEOUT * 2 should be less than nginx timeout
    const OPERATION_TIMEOUT = 50; // should be less than nginx timeout

    /**
     * modify IPD-API config to Ldap library
     * @param $encoded
     * @return array
     */
    public function getConfig(string $encoded): array
    {
        $config = $this->decode($encoded);
        if (empty($config)) {
            return [];
        }
        $ldap = [
            'adapter_config' => [
                'host' => parse_url($config['server'], PHP_URL_HOST),
                'port' => parse_url($config['server'], PHP_URL_PORT),
                'options' => [
                    'network_timeout' => self::NETWORK_TIMEOUT,
                    'timelimit' => self::OPERATION_TIMEOUT,
                    'timeout' => self::NETWORK_TIMEOUT,
                ],
                'encryption' => parse_url($config['server'], PHP_URL_SCHEME) === 'ldaps' ? 'ssl' : 'none',
            ],
            'adapter_connection_protocol_version' => 3,
            'baseDn' => (string)$config['user_dn'],
            'uidKey' => (string)$config['login_attribute'],
            'filter' => $this->buildLdapSearchFilter($config),
            'dnString' => null,
            'entryAttribute' => (string)$config['bind_attribute'],
            'auto_create_users' => (bool)$config['auto_create_users'],
        ];

        if (!empty($config['authentication'])) {
            $ldap['searchDn'] = (string)$config['authentication_user_dn'];
            $ldap['searchPassword'] = (string)$config['authentication_password'];
        }
        if (!empty($config['group_membership'])) {
            $ldap['groupMembership'] = true;
            $ldap['groupDn'] = sprintf(
                '%s,%s',
                (string)$config['group_name'],
                (string)$config['group_dn']
            );
            $ldap['groupAttribute'] = (string)$config['group_attribute'];
            $ldap['userUniqueAttribute'] = (string)$config['user_unique_attribute'];
            $ldap['includeUserDN'] = (bool)$config['include_user_dn'];
        }
        return $ldap;
    }

    /**
     * @param array $config
     * @return string
     */
    protected function buildLdapSearchFilter(array $config): string
    {
        $defaultFilter = '({uid_key}={username})';
        $loginFilter = trim((string)$config['user_filter'], " ()\t\n\r\0\x0B");
        if (!empty($loginFilter)) {
            $loginFilter = '(' . $loginFilter . ')';
            return '(&' . $defaultFilter . $loginFilter . ')';
        }
        return $defaultFilter;
    }
}
