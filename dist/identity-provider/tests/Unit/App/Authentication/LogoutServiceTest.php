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

use Pimple\Container;
use Psr\Log\LoggerInterface;
use Sugarcrm\IdentityProvider\App\Authentication\CookieService;
use Sugarcrm\IdentityProvider\App\Authentication\LogoutService;
use Sugarcrm\IdentityProvider\App\Authentication\RedirectURLService;
use Sugarcrm\IdentityProvider\App\Authentication\RememberMe\Service;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeTokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class LogoutServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LogoutService
     */
    private $logoutService;

    /**
     * @var Service | \PHPUnit_Framework_MockObject_MockObject
     */
    private $rememberMe;

    /**
     * @var CookieService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieService;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var RememberMeTokenInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $rememberMeToken;

    /**
     * @var RedirectURLService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectURLService;

    /**
     * @var LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        $config = [
            'cookie.options' => [
                'domain' => 'staging.sugarcrm.io',
            ]
        ];

        $this->cookieService = $this->createMock(CookieService::class);
        $this->redirectURLService = $this->createMock(RedirectURLService::class);

        $this->redirectURLService
            ->method('getRedirectURL')
            ->willReturn('/login');

        $this->rememberMe = $this->createMock(Service::class);
        $this->rememberMeToken = $this->createMock(RememberMeToken::class);
        $this->session = $this->createMock(Session::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $app = $this->createMock(Container::class);
        $app->method('offsetGet')->willReturnMap([
            ['redirectURLService', $this->redirectURLService],
            ['RememberMe', $this->rememberMe],
            ['config', $config],
            ['session', $this->session],
            ['cookies', $this->cookieService],
            ['logger', $this->logger],
        ]);

        $this->logoutService = new LogoutService($app);

        parent::setUp();
    }

    /**
     * @covers ::logout
     * @return void
     */
    public function testLogout(): void
    {
        $request = new Request();
        $this->rememberMe
            ->expects($this->once())
            ->method('retrieve')->willReturn($this->rememberMeToken);
        $this->rememberMe->expects($this->never())->method('retrieveBySrn');

        $this->rememberMeToken->expects($this->once())->method('setLoggedOut');

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with(TenantConfigInitializer::SESSION_KEY)
            ->willReturn(true);
        $this->session
            ->expects($this->once())
            ->method('remove')
            ->with(TenantConfigInitializer::SESSION_KEY);

        $response = $this->logoutService->logout($request);

        $this->assertEquals('/login', $response->getTargetUrl());
    }

    /**
     * @covers ::logout
     * @return void
     */
    public function testLogoutWithSpecificUser(): void
    {
        $userSrn = 'srn:dev:iam::2000000001:user:seed_max_id';

        $this->cookieService
            ->expects($this->once())
            ->method('setLoggedInIdentitiesCookie')
            ->with($this->isInstanceOf(Response::class));

        $this->rememberMe->expects($this->never())->method('retrieve');
        $this->rememberMe
            ->expects($this->once())
            ->method('retrieveBySrn')
            ->with($userSrn)
            ->willReturn($this->rememberMeToken);

        $this->rememberMeToken->expects($this->once())->method('setLoggedOut');

        $this->session
            ->expects($this->once())
            ->method('has')
            ->with(TenantConfigInitializer::SESSION_KEY)
            ->willReturn(true);
        $this->session
            ->expects($this->once())
            ->method('remove')
            ->with(TenantConfigInitializer::SESSION_KEY);

        $response = $this->logoutService->logout(new Request(), $userSrn);

        $this->assertEquals('/login', $response->getTargetUrl());
    }

    /**
     * @covers ::logout
     */
    public function testLogoutWithoutUserSession(): void
    {
        $userSrn = 'srn:dev:iam::2000000001:user:seed_max_id';
        $this->rememberMe
            ->expects($this->once())
            ->method('retrieveBySrn')
            ->with($userSrn)
            ->willReturn(null);

        $this->logger->expects($this->once())->method('warning');

        $response = $this->logoutService->logout(new Request(), $userSrn);
        $this->assertEquals('/login', $response->getTargetUrl());
    }
}
