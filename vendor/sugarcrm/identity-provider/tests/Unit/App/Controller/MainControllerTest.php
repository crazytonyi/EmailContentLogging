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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Controller;

use Grpc\UnaryCall;
use Psr\Log\LoggerInterface;

use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Authentication\CookieService;
use Sugarcrm\IdentityProvider\App\Authentication\ConsentRequest\ConsentToken;
use Sugarcrm\IdentityProvider\App\Authentication\RememberMe\Service as RememberMe;
use Sugarcrm\IdentityProvider\App\Controller\MainController;
use Sugarcrm\IdentityProvider\App\Grpc\AppService;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\App\Regions\RegionChecker;
use Sugarcrm\IdentityProvider\App\Regions\TenantRegion;
use Sugarcrm\IdentityProvider\App\Repository\TenantRepository;
use Sugarcrm\IdentityProvider\App\Repository\UserRepository;
use Sugarcrm\IdentityProvider\App\ServiceDiscovery;
use Sugarcrm\IdentityProvider\App\TenantConfiguration;
use Sugarcrm\IdentityProvider\App\User\PasswordChecker;

use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Sugarcrm\IdentityProvider\Authentication\Tenant;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Authentication\Token\UsernamePasswordTokenFactory;
use Sugarcrm\IdentityProvider\Srn;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Controller\MainController
 */
class MainControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $application;

    /**
     * @var AppService | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $grpcAppService;

    /**
     * @var UnaryCall | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $unaryCall;

    /**
     * @var LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var Request | \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var RememberMe | \PHPUnit_Framework_MockObject_MockObject
     */
    private $rememberMe;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var TenantRegion | \PHPUnit_Framework_MockObject_MockObject
     */
    private $tenantRegion;

    /**
     * @var UserRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepository;

    /**
     * @var ServiceDiscovery | \PHPUnit_Framework_MockObject_MockObject
     */
    private $discovery;

    /**
     * @var array
     */
    private $config = [
        'idm' => [
            'region' => 'na',
        ],
        'grpc' => [
            'disabled' => false,
        ],
    ];

    /**
     * @var string
     */
    private $userName = 'user_name.value';

    /**
     * @var string
     */
    private $password = 'password.value';

    /**
     * @var MainController
     */
    private $mainController;

    /**
     * @var CsrfTokenManager| \PHPUnit_Framework_MockObject_MockObject
     */
    private $csrfTokenManager;

    /**
     * @var RecursiveValidator | \PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var TenantRepository| \PHPUnit_Framework_MockObject_MockObject
     */
    private $tenantRepository;

    /**
     * @var Srn\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    private $srnManager;

    /**
     * @var TenantConfiguration| \PHPUnit_Framework_MockObject_MockObject
     */
    private $tenantConfiguration;

    /**
     * @var CookieService| \PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieService;

    /**
     * @var UsernamePasswordTokenFactory| \PHPUnit_Framework_MockObject_MockObject
     */
    private $usernamePasswordTokenFactory;

    /**
     * @var string
     */
    private $tid = '2000000001';

    /**
     * @var Srn\Srn
     */
    private $tenantSrn;

    /**
     * @var string
     */
    private $csrfToken = 'csrf_token.value';

    /**
     * @var string
     */
    private $region = 'na';

    /**
     * @var TokenInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $token;

    /**
     * @var AuthenticationProviderManager | \PHPUnit_Framework_MockObject_MockObject
     */
    private $authManager;

    /**
     * @var User | \PHPUnit_Framework_MockObject_MockObject
     */
    private $user;

    /**
     * @var PasswordChecker | \PHPUnit_Framework_MockObject_MockObject
     */
    private $passwordChecker;

    /**
     * @var UrlGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlGenerator;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cookieService = $this->createMock(CookieService::class);
        $this->urlGenerator = $this->createMock(UrlGenerator::class);
        $this->passwordChecker = $this->createMock(PasswordChecker::class);

        $this->user = $this->createMock(User::class);
        $this->user->method('getLocalUser')->willReturn($this->user);
        $this->user->method('getAttribute')->willReturnMap([
            ['id', 'user-id']
        ]);

        $this->token = $this->createMock(UsernamePasswordToken::class);
        $this->token->method('getUsername')->willReturn($this->userName);
        $this->token->method('getCredentials')->willReturn($this->password);
        $this->token->method('isAuthenticated')->willReturn(true);
        $this->token->method('getUser')->willReturn($this->user);
        $this->token->method('getProviderKey')->willReturn(Providers::PROVIDER_KEY_LOCAL);

        $this->authManager = $this->createMock(AuthenticationProviderManager::class);
        $this->usernamePasswordTokenFactory = $this->createMock(UsernamePasswordTokenFactory::class);
        $this->usernamePasswordTokenFactory->method('createAuthenticationToken')->willReturn($this->token);
        $this->tenantConfiguration = $this->createMock(TenantConfiguration::class);
        $this->tenantRepository = $this->createMock(TenantRepository::class);
        $tenant = Tenant::fromArray(
            [
                'id' => $this->tid,
                'region' => $this->region,
                'display_name' => 'displayName',
                'logo' => 'logo',
                'status' => Tenant::STATUS_ACTIVE,
            ]
        );
        $this->tenantRepository
            ->method('findTenantById')
            ->willReturnMap([
                [$this->tid, $tenant],
            ]);

        $this->srnManager = $this->createMock(Srn\Manager::class);
        $this->tenantSrn = Srn\Converter::fromString(Srn\SrnRules::SCHEME . ":cloud:idp:{$this->region}:{$this->tid}:tenant");
        $this->srnManager->method('createTenantSrn')
            ->willReturnMap([
                [$this->tid, $this->tenantSrn],
            ]);
        $userSrn = Srn\Converter::fromString(Srn\SrnRules::SCHEME . ":cloud:idp:{$this->region}:{$this->tid}:user:user-id");
        $this->srnManager->method('createUserSrn')
            ->willReturnMap([
                [$this->tid, 'user-id', $userSrn],

                //testGetAllTokens
                ['2000000001', '002', Srn\Converter::fromString(Srn\SrnRules::SCHEME . ":cloud:idp::2000000001:user:002")],
                ['2000000002', '012', Srn\Converter::fromString(Srn\SrnRules::SCHEME . ":cloud:idp::2000000002:user:012")],
                ['2000000002', '013', Srn\Converter::fromString(Srn\SrnRules::SCHEME . ":cloud:idp::2000000002:user:013")],
            ]);

        $this->csrfTokenManager = $this->createMock(CsrfTokenManager::class);
        $this->validator = $this->createMock(RecursiveValidator::class);
        $this->rememberMe = $this->createMock(RememberMe::class);
        $this->session = $this->createMock(Session::class);
        $this->tenantRegion = $this->createMock(TenantRegion::class);
        $this->discovery = $this->createMock(ServiceDiscovery::class);

        $this->request = $this->createMock(Request::class);
        $this->request->request = $this->createMock(ParameterBag::class);
        $this->request->headers = $this->createMock(HeaderBag::class);
        $this->request->query = $this->createMock(ParameterBag::class);
        $this->request->method('getSession')->willReturn($this->session);

        $this->unaryCall = $this->createMock(UnaryCall::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->regionChecker = $this->createMock(RegionChecker::class);

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->grpcAppService = $this->createMock(AppService::class);

        $this->application = $this->createPartialMock(
            Application::class,
            [
                'getConfig',
                'getLogger',
                'getRememberMeService',
                'getSession',
                'getTenantRegion',
                'getServiceDiscovery',
                'getRegionChecker',
                'getCsrfTokenManager',
                'getValidatorService',
                'getTenantRepository',
                'getSrnManager',
                'getTenantConfiguration',
                'getUsernamePasswordTokenFactory',
                'getAuthManagerService',
                'getUserPasswordChecker',
                'getUrlGeneratorService',
                'getCookieService',
                'getUserRepository',
                'getGrpcAppService',
            ]
        );
        $this->application->method('getLogger')->willReturn($this->logger);
        $this->application->method('getRememberMeService')->willReturn($this->rememberMe);
        $this->application->method('getSession')->willReturn($this->session);
        $this->application->method('getTenantRegion')->willReturn($this->tenantRegion);
        $this->application->method('getConfig')->willReturn($this->config);
        $this->application->method('getServiceDiscovery')->willReturn($this->discovery);
        $this->application->method('getRegionChecker')->willReturn($this->regionChecker);
        $this->application->method('getCsrfTokenManager')->willReturn($this->csrfTokenManager);
        $this->application->method('getValidatorService')->willReturn($this->validator);
        $this->application->method('getTenantRepository')->willReturn($this->tenantRepository);
        $this->application->method('getSrnManager')->willReturn($this->srnManager);
        $this->application->method('getTenantConfiguration')->willReturn($this->tenantConfiguration);
        $this->application->method('getAuthManagerService')->willReturn($this->authManager);
        $this->application->method('getUrlGeneratorService')->willReturn($this->urlGenerator);
        $this->application->method('getCookieService')->willReturn($this->cookieService);
        $this->application->method('getUserRepository')->willReturn($this->userRepository);
        $this->application->method('getGrpcAppService')->willReturn($this->grpcAppService);

        $this->application['config'] = $this->config;

        $this->mainController = new MainController();
    }

    public function testRedirectToTenantCrmWithoutTid()
    {
        $this->grpcAppService
            ->expects($this->never())
            ->method('getCrmUrlByTenant');

        $response = MainController::redirectToTenantCrm($this->application, '');
        $this->assertNull($response);
    }

    public function testRedirectToTenantCrm()
    {
        $this->grpcAppService
            ->expects($this->once())
            ->method('getCrmUrlByTenant')
            ->with('1000000001')
            ->willReturn('https://test.crm.com:8080/crm/');

        $response = MainController::redirectToTenantCrm($this->application, '1000000001');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://test.crm.com:8080/crm/', $response->getTargetUrl());
    }

    public function testRedirectToTenantCrmWithError()
    {
        $this->grpcAppService
            ->expects($this->once())
            ->method('getCrmUrlByTenant')
            ->with('1000000001')
            ->willReturn(null);

        $response = MainController::redirectToTenantCrm($this->application, '1000000001');

        $this->assertNull($response);
    }

    /**
     * @covers ::renderFormAction
     */
    public function testRedirectToDifferentRegion()
    {
        $tenantString = '2000000001';
        $tenantRegion = 'eu';
        $loginServiceURL = 'https://eu.login.sugar.multiverse';

        $mainController = new MainController($this->application);

        $this->request
            ->method('get')
            ->willReturnMap([[TenantConfigInitializer::REQUEST_KEY, null, $tenantString]]);
        $this->request->query->method('has')->willReturnMap([['login_hint', false]]);
        $this->tenantRegion
            ->method('getRegion')
            ->with($this->equalTo($tenantString))
            ->willReturn($tenantRegion);
        $this->regionChecker->expects($this->once())
            ->method('redirectToRegion')
            ->willReturn($this->createMock(RedirectResponse::class));

        /** @var RedirectResponse $response */
        $response = $mainController->renderFormAction($this->application, $this->request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }


    /**
     * @return array
     * @see testPostFormActionAuthenticated
     */
    public function postFormActionAuthenticatedDataProvider(): array
    {
        return [
            'without_consent' => [
                'in' => [
                    'consentToken' => null,
                    'redirectUrl' => '/login-end-point',
                    'from' => null,
                ],
                'expectedRoute' => 'loginEndPoint',
                'setTenantCookie' => 1,
            ],
            'with_consent' => [
                'in' => [
                    'consentToken' => $this->createMock(ConsentToken::class),
                    'redirectUrl' => '/consent/confirmation',
                    'from' => null,
                ],
                'expectedRoute' => 'consentConfirmation',
                'setTenantCookie' => 1,
            ],
            'withFrom' => [
                'in' => [
                    'consentToken' => null,
                    'redirectUrl' => '/changePassword',
                    'from' => 'changePassword',
                ],
                'expectedRoute' => 'showChangePasswordForm',
                'setTenantCookie' => 0,
            ]
        ];
    }

    /**
     * @covers ::postFormAction
     * @dataProvider postFormActionAuthenticatedDataProvider
     * @param array $in
     * @param string $expectedRoute
     * @param int $setTenantCookie
     */
    public function testPostFormActionAuthenticated(array $in, string $expectedRoute, int $setTenantCookie)
    {
        $this->passwordChecker->method('isPasswordExpired')->willReturn(false);
        $this->session
            ->method('get')
            ->willReturnMap(
                [
                    [TenantConfigInitializer::SESSION_KEY, null, Srn\Converter::toString($this->tenantSrn)],
                    ['consent', null, $in['consentToken']],
                    ['from', null, $in['from']],
                ]
            );

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($expectedRoute)
            ->willReturn($in['redirectUrl']);
        $this->cookieService
            ->expects($this->exactly($setTenantCookie))
            ->method('setTenantCookie')
            ->with(
                $this->isInstanceOf(RedirectResponse::class),
                $this->equalTo(Srn\Converter::toString($this->tenantSrn))
            );
        $this->authManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($this->equalTo($this->token))
            ->willReturn($this->token);
        $this->application->expects($this->once())
            ->method('getUsernamePasswordTokenFactory')
            ->with($this->userName, $this->password)
            ->willReturn($this->usernamePasswordTokenFactory);
        $this->request->method('get')->willReturnMap([
            ['tid', null, $this->tid],
            ['user_name', null, $this->userName],
            ['password', null, $this->password],
            ['csrf_token', null, $this->csrfToken],
        ]);
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with([
                'tid' => $this->tid,
                'user_name' => $this->userName,
                'password' => $this->password,
                'csrf_token' => $this->csrfToken,
            ])
            ->willReturn([]);

        $this->rememberMe->method('retrieve')
            ->willReturn(new RememberMeToken($this->token));

        $response = $this->mainController->postFormAction($this->application, $this->request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($in['redirectUrl'], $response->getTargetUrl());
    }

    /**
     * @covers ::getAllTokens
     */
    public function testGetAllTokens()
    {
        $tokensAll = [];
        $users = [
            [
                'id' => '001',
                'srn' => 'srn:cloud:iam::2000000001:user:001',
                'userName' => 'user001',
                'password' => 'password001',
            ],
            [
                'id' => '002',
                'srn' => 'srn:cloud:iam::2000000001:user:002',
                'userName' => 'user002',
                'password' => 'password002',
            ],
            [
                'id' => '003',
                'srn' => 'srn:cloud:iam::2000000001:user:003',
                'userName' => 'user003',
                'password' => 'password003',
            ],
            [
                'id' => '011',
                'srn' => 'srn:cloud:iam::2000000002:user:011',
                'userName' => 'user011',
                'password' => 'password011',
            ],
            [
                'id' => '012',
                'srn' => 'srn:cloud:iam::2000000002:user:012',
                'userName' => 'user012',
                'password' => 'password012',
            ],
            [
                'id' => '013',
                'srn' => 'srn:cloud:iam::2000000002:user:013',
                'userName' => 'user013',
                'password' => 'password013',
            ],
            [
                'id' => '014',
                'srn' => '-invalid-user-SRN-',
                'userName' => 'user014',
                'password' => 'password014',
            ],
        ];
        foreach ($users as $uArr) {
            $user = $this->createMock(User::class);
            $user->method('getLocalUser')->willReturn($user);
            $user->method('getAttribute')->willReturnMap([
                ['id', $uArr['id']],
            ]);
            $token = $this->createMock(UsernamePasswordToken::class);
            $token->method('getUsername')->willReturn($uArr['userName']);
            $token->method('getCredentials')->willReturn($uArr['password']);
            $token->method('isAuthenticated')->willReturn(true);

            $token->method('hasAttribute')->willReturn(true);
            $token->method('getAttribute')->willReturnMap([
                ['srn', $uArr['srn']],
            ]);
            $token->method('getUser')->willReturn($user);
            $token->method('getProviderKey')->willReturn(Providers::PROVIDER_KEY_LOCAL);

            $tokensAll[$uArr['id']] = new RememberMeToken($token);
        }
        $tokensActive =
        [
            $tokensAll['001'],
            $tokensAll['003'],
            $tokensAll['011'],
        ];

        $this->rememberMe->method('list')
            ->will($this->onConsecutiveCalls($tokensAll, $tokensActive));

        $this->userRepository
            ->expects($this->exactly(2))
            ->method('findActiveUserIDsForTenant')
            ->will($this->returnValueMap([
                ['2000000001', ['001', '002', '003'], ['001', '003']],
                ['2000000002', ['011', '012', '013'], ['011']],
            ]));

        $this->rememberMe
            ->method('retrieveBySrn')
            ->will($this->returnValueMap([
                ['srn:cloud:idp::2000000001:user:002', $tokensAll['002']],
                ['srn:cloud:idp::2000000002:user:012', $tokensAll['012']],
                ['srn:cloud:idp::2000000002:user:013', null],
            ]));

        $this->rememberMe
            ->expects($this->exactly(3))
            ->method('remove')
            ->withConsecutive(
                [$tokensAll['002']],
                [$tokensAll['012']],
                [$tokensAll['014']]
            );

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with(
                'Token without valid user SRN',
                [
                    'violation' => 'Invalid number of components in SRN',
                    'invalidSRN' => '-invalid-user-SRN-',
                ]
            );

        $r = new \ReflectionMethod(MainController::class, 'getAllTokens');
        $r->setAccessible(true);
        $res = $r->invoke(new MainController(), $this->application);

        $this->assertEquals($tokensActive, $res);
    }
}
