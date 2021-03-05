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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Authentication\ConfigAdapter;

use PHPUnit\Framework\TestCase;
use Sugarcrm\IdentityProvider\App\Authentication\ConfigAdapter\OidcConfigAdapter;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Authentication\ConfigAdapter\OidcConfigAdapter
 */
final class OidcConfigAdapterTest extends TestCase
{
    public function getConfigProvider(): array
    {
        return [
            'oidcConfigWithoutUserInfo' => [
                'config' => '{
                    "authentication_endpoint":"https://site/auth",
                    "token_endpoint":"https://site/oauth/token",
                    "client_id":"id",
                    "client_secret":"secret",
                    "scopes":["profile","email","openid"],
                    "provision_user":true
                    }',
                'expected' => [
                    'urlAuthorize' => 'https://site/auth',
                    'urlAccessToken' => 'https://site/oauth/token',
                    'urlResourceOwnerDetails' => 'https://site/oauth/token',
                    'urlUserInfo' => null,
                    'clientId' => 'id',
                    'clientSecret' => 'secret',
                    'scope' => ['profile', 'email', 'openid'],
                    'provisionUser' => true,
                ]
            ],
            'oidcConfigWithoutScopes' => [
                'config' => '{
                    "authentication_endpoint":"https://site/auth",
                    "token_endpoint":"https://site/oauth/token",
                    "userinfo_endpoint":"https://site/oauth/info",
                    "client_id":"id",
                    "client_secret":"secret",
                    "provision_user":true
                    }',
                'expected' => [
                    'urlAuthorize' => 'https://site/auth',
                    'urlAccessToken' => 'https://site/oauth/token',
                    'urlResourceOwnerDetails' => 'https://site/oauth/token',
                    'urlUserInfo' => 'https://site/oauth/info',
                    'clientId' => 'id',
                    'clientSecret' => 'secret',
                    'scope' => [],
                    'provisionUser' => true,
                ]
            ],
            'oidcConfigWithoutProvisioning' => [
                'config' => '{
                    "authentication_endpoint":"https://site/auth",
                    "token_endpoint":"https://site/oauth/token",
                    "userinfo_endpoint":"https://site/oauth/info",
                    "client_id":"id",
                    "client_secret":"secret"
                    }',
                'expected' => [
                    'urlAuthorize' => 'https://site/auth',
                    'urlAccessToken' => 'https://site/oauth/token',
                    'urlResourceOwnerDetails' => 'https://site/oauth/token',
                    'urlUserInfo' => 'https://site/oauth/info',
                    'clientId' => 'id',
                    'clientSecret' => 'secret',
                    'scope' => [],
                    'provisionUser' => false,
                ]
            ]
        ];
    }

    /**
     * @param string $config
     * @param array $expected
     *
     * @covers ::getConfig
     * @dataProvider getConfigProvider
     */
    public function testGetConfig(string $config, array $expected): void
    {
        $configAdapter = new OidcConfigAdapter();
        $this->assertEquals($expected, $configAdapter->getConfig($config));
    }
}
