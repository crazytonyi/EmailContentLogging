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

use Doctrine\DBAL\Connection;
use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Authentication\CookieService;
use Sugarcrm\IdentityProvider\App\Authentication\RememberMe\Service;
use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\UserProviderBuilder;
use Sugarcrm\IdentityProvider\Authentication\Audit;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Authentication\CookieService
 */
class CookieServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var CookieService
     */
    private $cookieService;

    /**
     * @var Service
     */
    private $rememberMeService;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->request = new Request();
        $this->response = new Response();
        $config = [
            'cookie.options' => [
                'secure' => true,
                'domain' => 'domain.test',
                'logged_cookie_name' => 'lgn'
            ],
        ];

        $this->rememberMeService = new Service(
            new Session(new MockArraySessionStorage()),
            $this->createMock(UserProviderBuilder::class)
        );

        /** @var Application | \PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->createMock(Application::class);
        $app->method('offsetGet')->willReturnMap([
            ['config', $config],
        ]);

        $app->method('getRememberMeService')->willReturn($this->rememberMeService);

        $this->cookieService = new CookieService($app, 'localeName');

        parent::setUp();
    }

    /**
     * @return array
     */
    public function providerCookie(): array
    {
        $cookieOptions = ['secure' => true, 'domain' => '.domain.test', 'logged_cookie_name' => 'lgn'];

        return [
            'cookie domain with dot' => [
                'in' => [
                    'config' =>
                        [
                            'cookie.options' => $cookieOptions,
                        ],
                    'value' => 'testValue',
                    'localeCookieName' => 'locale',
                    'samlTidCookieName' => 'samlTid',
                ],
                'expected' => [
                    'domain' => '.domain.test'
                ],
            ],
            'cookie domain without dot' => [
                'in' => [
                    'config' =>
                        [
                            'cookie.options' => ['domain' => 'domain.test'] + $cookieOptions,
                        ],
                    'value' => 'testValue',
                    'localeCookieName' => 'locale',
                    'samlTidCookieName' => 'samlTid',
                ],
                'expected' => [
                    'domain' => '.domain.test'
                ],
            ],
            'cookie domain empty' => [
                'in' => [
                    'config' =>
                        [
                            'cookie.options' => ['domain' => ''] + $cookieOptions,
                        ],
                    'value' => 'testValue',
                    'localeCookieName' => 'locale',
                    'samlTidCookieName' => 'samlTid',
                ],
                'expected' => [
                    'domain' => null,
                ],
            ],
            'not secure cookie' => [
                'in' => [
                    'config' =>
                        [
                            'cookie.options' => ['secure' => false] + $cookieOptions,
                        ],
                    'value' => 'testValue',
                    'localeCookieName' => 'locale',
                    'samlTidCookieName' => 'samlTid',
                ],
                'expected' => [
                    'domain' => '.domain.test',
                ],
            ],
        ];
    }

    /**
    * @return void
    * @dataProvider providerCookie
    * @covers ::__construct
    * @covers ::setTenantCookie
    * @covers ::setCookie
    * @covers ::getCookieDomain
    * @param array $in
    * @param array $expected
    */
    public function testSetTenantCookie(array $in, array $expected): void
    {
        $cookieService = $this->createCookieService($in['config'], $in['localeCookieName']);
        $cookieService->setTenantCookie($this->response, $in['value']);

        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies);

        $tidCookie = $cookies[0];
        $this->assertEquals(CookieService::TENANT_COOKIE_NAME, $tidCookie->getName());
        $this->assertEquals($in['value'], $tidCookie->getValue());
        $this->assertEquals($in['config']['cookie.options']['secure'], $tidCookie->isSecure());
        $this->assertEquals($expected['domain'], $tidCookie->getDomain());
    }

    /**
     * @covers ::getTenantCookie
     */
    public function testGetTenantCookie(): void
    {
        $this->request->cookies->add([CookieService::TENANT_COOKIE_NAME => 'testValue']);
        $this->assertEquals('testValue', $this->cookieService->getTenantCookie($this->request));
    }

    /**
     * @return void
     * @dataProvider providerCookie
     * @covers ::__construct
     * @covers ::setRegionCookie
     * @covers ::setCookie
     * @covers ::getCookieDomain
     * @param array $in
     * @param array $expected
     */
    public function testSetRegionCookie(array $in, array $expected): void
    {
        $cookieService = $this->createCookieService($in['config'], $in['localeCookieName']);
        $cookieService->setRegionCookie($this->response, $in['value']);

        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies);

        $regionCookie = $cookies[0];
        $this->assertEquals(CookieService::REGION_COOKIE_NAME, $regionCookie->getName());
        $this->assertEquals($in['value'], $regionCookie->getValue());
        $this->assertEquals($in['config']['cookie.options']['secure'], $regionCookie->isSecure());
        $this->assertEquals($expected['domain'], $regionCookie->getDomain());
    }

    /**
     * @covers ::getRegionCookie
     */
    public function testGetRegionCookie(): void
    {
        $this->request->cookies->add([CookieService::REGION_COOKIE_NAME => 'testValueRegion']);
        $this->assertEquals('testValueRegion', $this->cookieService->getRegionCookie($this->request));
    }

    /**
     * Region cookie delete logic test
     *
     * @return void
     * @dataProvider providerCookie
     * @covers ::__construct
     * @covers ::clearRegionCookie
     * @covers ::clearCookie
     * @covers ::getCookieDomain
     *
     * @param array $in
     * @param array $expected
     */
    public function testClearRegionCookie(array $in, array $expected): void
    {
        $cookieService = $this->createCookieService($in['config'], $in['localeCookieName']);
        $cookieService->clearRegionCookie($this->response);

        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies);

        $logoutCookie = $cookies[0];
        $this->assertEquals(CookieService::REGION_COOKIE_NAME, $logoutCookie->getName());
        $this->assertEquals($in['config']['cookie.options']['secure'], $logoutCookie->isSecure());
        $this->assertEquals($expected['domain'], $logoutCookie->getDomain());
        $this->assertLessThan(time(), $logoutCookie->getExpiresTime());
    }

    /**
     * @return void
     * @dataProvider providerCookie
     * @covers ::__construct
     * @covers ::setLocaleCookie
     * @covers ::setCookie
     * @covers ::getCookieDomain
     * @param array $in
     * @param array $expected
     */
    public function testSetLocaleCookie(array $in, array $expected): void
    {
        $cookieService = $this->createCookieService($in['config'], $in['localeCookieName']);
        $cookieService->setLocaleCookie($this->response, $in['value']);

        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies);

        $localeCookie = $cookies[0];
        $this->assertEquals($in['localeCookieName'], $localeCookie->getName());
        $this->assertNotEmpty($localeCookie->getValue());
        $this->assertEquals($in['config']['cookie.options']['secure'], $localeCookie->isSecure());
        $this->assertEquals($expected['domain'], $localeCookie->getDomain());
    }

    public function testSetUICookie(): void
    {
        $cookieService = $this->createCookieService(
            ['cookie.options' => ['secure' => true, 'domain' => '.domain.test', 'logged_cookie_name' => 'lgn']],
            'locale'
        );

        $cookieService->setUICookie($this->response, 'en-US');

        $uiCookie = $this->response->headers->getCookies()[0];
        $this->assertEquals('.domain.test', $uiCookie->getDomain());
        $this->assertEquals('en-US', $uiCookie->getValue());
    }

    /**
     * @return void
     * @dataProvider providerCookie
     * @covers ::__construct
     * @covers ::setSamlTenantCookie
     * @covers ::setCookie
     * @covers ::getCookieDomain
     * @param array $in
     * @param array $expected
     */
    public function testSetSamlTenantCookie(array $in, array $expected): void
    {
        $cookieService = $this->createCookieService($in['config'], $in['localeCookieName']);
        $cookieService->setSamlTenantCookie($this->response, $in['value']);

        $cookies = $this->response->headers->getCookies();
        $this->assertCount(1, $cookies);

        $samlCookie = $cookies[0];
        $this->assertEquals($in['samlTidCookieName'], $samlCookie->getName());
        $this->assertNotEmpty($samlCookie->getValue());
        $this->assertEquals($in['config']['cookie.options']['secure'], $samlCookie->isSecure());
        $this->assertEquals($expected['domain'], $samlCookie->getDomain());
        $this->assertEquals(0, $samlCookie->getExpiresTime());
    }

    /**
     * @covers ::getLocaleCookie
     * @covers ::__construct
     */
    public function testGetLocaleCookie(): void
    {
        $this->request->cookies->add(['localeName' => 'testValueLocale']);
        $this->assertEquals('testValueLocale', $this->cookieService->getLocaleCookie($this->request));
    }

    /**
     * @param array $in
     * @param array $expected
     *
     * @covers ::setLoggedInIdentitiesCookie
     * @dataProvider providerCookie
     */
    public function testSetLoggedInIdentitiesCookieNoLoggedUsers(array $in, array $expected): void
    {
        $cookieService = $this->createCookieService($in['config'], $in['localeCookieName']);
        $cookieService->setLoggedInIdentitiesCookie($this->response);
        $cookies = $this->response->headers->getCookies();

        $loggedCookie = $cookies[0];
        $this->assertEquals($in['config']['cookie.options']['logged_cookie_name'], $loggedCookie->getName());
        $this->assertEmpty($loggedCookie->getValue());
        $this->assertEquals($in['config']['cookie.options']['secure'], $loggedCookie->isSecure());
        $this->assertEquals($expected['domain'], $loggedCookie->getDomain());
        $this->assertLessThan(time(), $loggedCookie->getExpiresTime());
    }

    /**
     * @param array $in
     * @param array $expected
     *
     * @covers ::setLoggedInIdentitiesCookie
     * @dataProvider providerCookie
     */
    public function testSetLoggedInIdentitiesCookieSeveralLoggedUsers(array $in, array $expected): void
    {
        $token1 = new RememberMeToken(
            new UsernamePasswordToken('user1', 'password1', 'local')
        );
        $token1->setAttribute('srn', 'srn:user1');

        $token2 = new RememberMeToken(
            new UsernamePasswordToken('user2', 'password1', 'local')
        );
        $token2->setAttribute('srn', 'srn:user2');

        $token3 = new RememberMeToken(
            new UsernamePasswordToken('user3', 'password1', 'local')
        );
        $token3->setAttribute('srn', 'srn:user3');

        $token4 = new RememberMeToken(
            new UsernamePasswordToken('user4', 'password1', 'local')
        );
        $token4->setAttribute('srn', 'srn:user4');

        $this->rememberMeService->store($token1);
        $this->rememberMeService->store($token2);
        $this->rememberMeService->store($token3);
        $this->rememberMeService->store($token4);

        $token2->setLoggedOut();
        $token3->setLoggedOut();

        $cookieService = $this->createCookieService($in['config'], $in['localeCookieName']);

        $cookieService->setLoggedInIdentitiesCookie($this->response);
        $cookies = $this->response->headers->getCookies();

        $loggedCookie = $cookies[0];
        $this->assertEquals($in['config']['cookie.options']['logged_cookie_name'], $loggedCookie->getName());
        $this->assertEquals('srn:user4|srn:user1', $loggedCookie->getValue());
        $this->assertEquals($in['config']['cookie.options']['secure'], $loggedCookie->isSecure());
        $this->assertEquals($expected['domain'], $loggedCookie->getDomain());
        $this->assertGreaterThan(time(), $loggedCookie->getExpiresTime());
    }

    /**
     * @covers ::setLoggedInIdentitiesCookie
     */
    public function testSetLoggedInIdentitiesCookieWithEncryption(): void
    {
        $token1 = new RememberMeToken(
            new UsernamePasswordToken('user1', 'password1', 'local')
        );
        $token1->setAttribute('srn', 'srn:user1');

        $token2 = new RememberMeToken(
            new UsernamePasswordToken('user2', 'password1', 'local')
        );
        $token2->setAttribute('srn', 'srn:user2');

        $this->rememberMeService->store($token1);
        $this->rememberMeService->store($token2);

        $config = [
            'cookie.options' => [
                'secure' => true,
                'domain' => '.domain.test',
                'logged_cookie_name' => 'lgn',
                'encryption_alg' => 'AES-256-CBC',
                'encryption_key' => '1234567890abcdef1234567890abcdef',
            ],
            'logout.options' => [
                'cookie_name' => 'logout',
            ]
        ];

        $cookieService = $this->createCookieService($config, 'locale');

        $cookieService->setLoggedInIdentitiesCookie($this->response);

        $cookies = $this->response->headers->getCookies();

        $cookieOptions = $config['cookie.options'];
        $loggedCookie = $cookies[0];

        $encryptedValue = base64_decode($loggedCookie->getValue());
        $iv = substr($encryptedValue, 0, openssl_cipher_iv_length($cookieOptions['encryption_alg']));
        $data = substr($encryptedValue, openssl_cipher_iv_length($cookieOptions['encryption_alg']));

        $result = hex2bin(openssl_decrypt(
            $data,
            $cookieOptions['encryption_alg'],
            $cookieOptions['encryption_key'],
            OPENSSL_RAW_DATA,
            $iv
        ));

        $this->assertEquals($cookieOptions['logged_cookie_name'], $loggedCookie->getName());
        $this->assertEquals('srn:user2|srn:user1', $result);
        $this->assertEquals($cookieOptions['secure'], $loggedCookie->isSecure());
        $this->assertEquals($cookieOptions['domain'], $loggedCookie->getDomain());
        $this->assertGreaterThan(time(), $loggedCookie->getExpiresTime());
    }

    private function createCookieService(array $config, string $localeCookieName): CookieService
    {
        /** @var Application | \PHPUnit_Framework_MockObject_MockObject $app */
        $app = $this->createMock(Application::class);
        $app->method('offsetGet')->willReturnMap([
            ['config', $config],
        ]);

        $app->method('getRememberMeService')->willReturn($this->rememberMeService);

        return new CookieService($app, $localeCookieName);
    }
}
