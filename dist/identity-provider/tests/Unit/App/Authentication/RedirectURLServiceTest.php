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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Authentication;

use Sugarcrm\IdentityProvider\App\Authentication\RedirectURLService;
use Sugarcrm\IdentityProvider\App\Grpc\AppService;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;

class RedirectURLServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlGenerator;

    /**
     * @var AppService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $appService;

    protected function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGenerator::class);
        $this->appService = $this->createMock(AppService::class);

        $this->urlGenerator
            ->method('generate')
            ->willReturnMap(
                [
                    ['loginRender', [], UrlGenerator::ABSOLUTE_URL, '/login'],
                ]
            );

        parent::setUp();
    }

    /**
     * Provides data for testGetRedirectUrl
     *
     * @return array
     */
    public function getRedirectUrlProvider(): array
    {
        return [
            'requestWithoutRedirectUriAndReferer' => [
                'query' => [],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => '/login',
            ],
            'requestWithoutRedirectUriAndRefererTwoComponentsDomain' => [
                'query' => [],
                'server' => [
                    'HTTP_HOST' => 'sugarcrm.io',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => '/login',
            ],
            'requestWithRedirectUriOnLoginService' => [
                'query' => [
                    'redirect_uri' => 'http://login.staging.sugarcrm.io/logout',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => '/login',
            ],
            'requestWithRedirectUri' => [
                'query' => [
                    'redirect_uri' => 'http://console.staging.sugarcrm.io/',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => 'http://console.staging.sugarcrm.io/',
            ],
            'requestWithLocalhostRedirectUri' => [
                'query' => [
                    'redirect_uri' => 'http://localhost:8080/callback',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => 'http://localhost:8080/callback',
            ],
            'requestWithLocalhostRedirectUri2' => [
                'query' => [
                    'redirect_uri' => 'https://localhost/callback',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => 'https://localhost/callback',
            ],
            'requestWithNotAllowedRedirectUri' => [
                'query' => [
                    'redirect_uri' => 'http://google.com/',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => '/login',
            ],
            'requestWithoutRedirectUriButWithReferer' => [
                'query' => [],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                    'HTTP_REFERER' => 'http://api.staging.sugarcrm.io/',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => 'http://api.staging.sugarcrm.io/',
            ],
            'requestWithoutRedirectUriButWithRefererOnLoginService' => [
                'query' => [],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                    'HTTP_REFERER' => 'http://login.staging.sugarcrm.io/logout',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => '/login',
            ],
            'requestToAllowedDomains' => [
                'query' => [
                    'redirect_uri' => 'http://allowed.domains.com:8080/callback',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => 'allowed.domains.com',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => 'http://allowed.domains.com:8080/callback',
            ],
            'requestToOneOfAllowedDomains' => [
                'query' => [
                    'redirect_uri' => 'http://allowed2.domain.com:8080/callback',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => 'allowed.domain.com,allowed2.domain.com',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => 'http://allowed2.domain.com:8080/callback',
            ],
            'requestToAllowedSubDomains' => [
                'query' => [
                    'redirect_uri' => 'http://sub.allowed.domains.com:8080/callback',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => 'allowed.domains.com',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => 'http://sub.allowed.domains.com:8080/callback',
            ],
            'requestToOneOfAllowedSubDomains' => [
                'query' => [
                    'redirect_uri' => 'http://sub.allowed2.domain.com:8080/callback',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => 'allowed.domain.com,allowed2.domain.com',
                'applicationsDomains' => [],
                'expectedRedirectUrl' => 'http://sub.allowed2.domain.com:8080/callback',
            ],
            'requestToOneOfAllowedApplicationsDomain' => [
                'query' => [
                    'redirect_uri' => 'http://allowed2.domain.com:8080/callback',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => ['allowed2.domain.com'],
                'expectedRedirectUrl' => 'http://allowed2.domain.com:8080/callback',
            ],
            'requestToOneOfNotApplicationsDomain' => [
                'query' => [
                    'redirect_uri' => 'http://allowed2.domain.com:8080/callback',
                ],
                'server' => [
                    'HTTP_HOST' => 'login.staging.sugarcrm.io',
                ],
                'allowedDomains' => '',
                'applicationsDomains' => ['allowed1.domain.com'],
                'expectedRedirectUrl' => '/login',
            ],
        ];
    }

    /**
     * @param array $query
     * @param array $server
     * @param string $allowedDomains
     * @param array $applicationsDomains
     * @param string $expectedRedirectUrl
     *
     * @dataProvider getRedirectUrlProvider
     *
     * @return void
     */
    public function testGetRedirectUrl(
        array $query,
        array $server,
        string $allowedDomains,
        array $applicationsDomains,
        string $expectedRedirectUrl
    ): void {
        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('has')
            ->with(TenantConfigInitializer::SESSION_KEY)
            ->willReturn(true);
        $session->expects($this->once())->method('get')
            ->with(TenantConfigInitializer::SESSION_KEY)
            ->willReturn('tenant');

        $this->appService->expects($this->once())
            ->method('getTenantApplicationsDomains')
            ->with('tenant')
            ->willReturn($applicationsDomains);

        $redirectURLService = new RedirectURLService($this->urlGenerator, $this->appService, $allowedDomains);

        $request = new Request($query, [], [], [], [], $server);
        $request->setSession($session);

        $redirect = $redirectURLService->getRedirectUrl($request);
        $this->assertEquals($expectedRedirectUrl, $redirect);
    }
}
