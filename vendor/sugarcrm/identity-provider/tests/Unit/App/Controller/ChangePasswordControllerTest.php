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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Driver\Statement;

use Grpc\UnaryCall;
use Sugarcrm\Apis\Iam\User\V1alpha\SetPasswordRequest;
use Sugarcrm\Apis\Iam\User\V1alpha\UserAPIClient;
use Sugarcrm\IdentityProvider\App\Authentication\CookieService;
use Sugarcrm\IdentityProvider\App\Authentication\OAuth2Service;
use Sugarcrm\IdentityProvider\App\Authentication\RedirectURLService;
use Sugarcrm\IdentityProvider\App\Authentication\RevokeAccessTokensService;
use Sugarcrm\IdentityProvider\App\Controller\ChangePasswordController;
use Sugarcrm\IdentityProvider\App\TenantConfiguration;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Sugarcrm\IdentityProvider\Srn\Manager;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\App\Authentication\RememberMe\Service;
use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\UserProviderBuilder;
use Sugarcrm\IdentityProvider\App\Constraints as CustomAssert;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Controller\ChangePasswordController
 */
class ChangePasswordControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | Application
     */
    protected $application;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | Request
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | OAuth2Service
     */
    protected $oauth2Service;


    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | Service
     */
    protected $rememberMeService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | FlashBagInterface
     */
    protected $flashBag;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | UrlGenerator
     */
    protected $urlGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Twig\Environment
     */
    protected $twig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | CsrfTokenManagerInterface
     */
    protected $csrfManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ValidatorInterface
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | PasswordEncoderInterface
     */
    protected $encoder;

    /**
     * @var ChangePasswordController
     */
    protected $controller;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var RedirectURLService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectUrlService;

    /**
     * @var array
     */
    private $config = [
        'local' => [
            'password_requirements' => [
                'minimum_length' => 3,
                'maximum_length' => 6,
                'require_upper' => true,
                'require_lower' => true,
                'require_number' => true,
                'require_special' => true,
            ],
        ],
        'grpc' => [
            'disabled' => true,
        ],
        'idm' => [
            'region' => 'na',
            'partition' => 'dev',
        ],
    ];

    /**
     * @var RevokeAccessTokensService | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $revokeAccessTokensService;

    protected function setUp()
    {
        $this->application = $this->createMock(Application::class);
        $this->request = $this->createMock(Request::class);

        $this->redirectUrlService = $this->createMock(RedirectURLService::class);
        $this->application->method('getRedirectURLService')->willReturn($this->redirectUrlService);

        $this->oauth2Service = $this->createMock(OAuth2Service::class);
        $this->application->method('getOAuth2Service')->willReturn($this->oauth2Service);

        $this->revokeAccessTokensService = $this->createMock(RevokeAccessTokensService::class);
        $this->application->method('getRevokeAccessTokensService')->willReturn($this->revokeAccessTokensService);

        $this->rememberMeService = $this->createMock(Service::class);
        $this->application->expects($this->any())->method('getRememberMeService')->willReturn($this->rememberMeService);

        $this->flashBag = $this->createMock(FlashBagInterface::class);
        $this->session = $this->createMock(Session::class);
        $this->session->expects($this->any())->method('getFlashBag')->willReturn($this->flashBag);
        $this->application->expects($this->any())->method('getSession')->willReturn($this->session);
        $this->application->method('getTenantConfiguration')
            ->willReturn($this->createMock(TenantConfiguration::class));

        $this->urlGenerator = $this->createMock(UrlGenerator::class);
        $this->application->expects($this->any())->method('getUrlGeneratorService')->willReturn($this->urlGenerator);

        $this->twig = $this->createMock(\Twig\Environment::class);
        $this->application->expects($this->any())->method('getTwigService')->willReturn($this->twig);

        $this->csrfManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->application->expects($this->any())->method('getCsrfTokenManager')->willReturn($this->csrfManager);

        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->application->expects($this->any())->method('getValidatorService')->willReturn($this->validator);

        $this->encoderFactory = $this->createMock(EncoderFactoryInterface::class);
        $this->encoder = $this->createMock(PasswordEncoderInterface::class);
        $this->application->expects($this->any())->method('getEncoderFactory')->willReturn($this->encoderFactory);

        $this->request->method('getSession')->willReturn($this->session);
        $this->request->cookies = $this->createMock(ParameterBag::class);
        $this->request->query = $this->createMock(ParameterBag::class);

        $this->controller = new ChangePasswordController();
    }

    /**
     * @covers ::preCheck
     */
    public function testPreCheckWithoutToken()
    {
        $this->rememberMeService->expects($this->once())
            ->method('retrieve')
            ->willReturn(null);

        $cookieService = $this->createMock(CookieService::class);
        $this->application->expects($this->once())
            ->method('getCookieService')
            ->willReturn($cookieService);

        $cookieService->expects($this->once())
            ->method('getTenantCookie')
            ->with($this->request)
            ->willReturn('srn:dev:iam:na:1678464015:tenant');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                'loginRender',
                ['tenant_hint' => 'srn:dev:iam:na:1678464015:tenant', 'from' => 'changePassword']
            )
            ->willReturn($url = 'http://login.test.url');
        $this->application->expects($this->once())
            ->method('redirect')
            ->with($url)
            ->willReturn(RedirectResponse::create($url));

        $this->controller->preCheck($this->request, $this->application);
    }

    /**
     * @covers ::preCheck
     */
    public function testPreCheckWrongToken()
    {
        $token = $this->createMock(RememberMeToken::class);
        $this->rememberMeService->expects($this->once())
            ->method('retrieve')
            ->willReturn($token);

        $this->flashBag->expects($this->once())
            ->method('add')
            ->with('error', 'Only local users can change password');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('loginRender')
            ->willReturn($url = 'http://login.test.url');
        $this->application->expects($this->once())
            ->method('redirect')
            ->with($url)
            ->willReturn(RedirectResponse::create($url));

        $this->controller->preCheck($this->request, $this->application);
    }

    /**
     * @covers ::preCheck
     */
    public function testPreCheckWrongProvider()
    {
        $token = new RememberMeToken(
            new UsernamePasswordToken('', '', Providers::PROVIDER_KEY_SAML)
        );
        $this->rememberMeService->expects($this->once())
            ->method('retrieve')
            ->willReturn($token);

        $this->flashBag->expects($this->once())
            ->method('add')
            ->with('error', 'Only local users can change password');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('loginRender')
            ->willReturn($url = 'http://login.test.url');
        $this->application->expects($this->once())
            ->method('redirect')
            ->with($url)
            ->willReturn(RedirectResponse::create($url));

        $this->controller->preCheck($this->request, $this->application);
    }

    /**
     * @covers ::preCheck
     */
    public function testPreCheckNoUser()
    {
        $token = new RememberMeToken(
            new UsernamePasswordToken('', '', Providers::PROVIDER_KEY_LOCAL)
        );
        $this->rememberMeService->expects($this->once())
            ->method('retrieve')
            ->willReturn($token);

        $this->flashBag->expects($this->once())
            ->method('add')
            ->with('error', 'No user is found');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('loginRender')
            ->willReturn($url = 'http://login.test.url');
        $this->application->expects($this->once())
            ->method('redirect')
            ->with($url)
            ->willReturn(RedirectResponse::create($url));

        $this->controller->preCheck($this->request, $this->application);
    }

    /**
     * @return array
     */
    public function preCheckWrongActiveUserDataProvider(): array
    {
        return [
            'same tenant, wrong user' => ['srn:dev:iam::1234567890:user:other_user_id'],
            'different tenant' => ['srn:dev:iam::1333333333:user:some_user_id']
        ];
    }

    /**
     * @dataProvider preCheckWrongActiveUserDataProvider
     * @covers ::preCheck
     */
    public function testPreCheckWrongActiveUser(string $activeUser)
    {
        $token = $this->createMock(RememberMeToken::class);
        $token->expects($this->once())
            ->method('getSource')
            ->willReturn($this->createMock(UsernamePasswordToken::class));
        $token->expects($this->once())
            ->method('getProviderKey')
            ->willReturn(Providers::PROVIDER_KEY_LOCAL);
        $user = $this->createMock(User::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $token->expects($this->once())
            ->method('getSRN')
            ->willReturn($activeUser);
        $this->rememberMeService->expects($this->once())
            ->method('retrieve')
            ->willReturn($token);

        $this->request->query->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $this->request->expects($this->once())
            ->method('get')
            ->willReturn('srn:dev:iam::1234567890:user:requested_user_id');

        $this->controller = $this->getMockBuilder(ChangePasswordController::class)
            ->setMethods(['getTenantHintFromUserHint', 'preCheckRedirect'])
            ->getMock();
        $this->controller->expects($this->once())
            ->method('getTenantHintFromUserHint')
            ->willReturn('srn:dev:iam:na:1234567890:tenant');
        $this->controller->expects($this->once())
            ->method('preCheckRedirect')
            ->with(
                $this->anything(),
                [
                    'from' => 'changePassword',
                    'user_hint' => 'srn:dev:iam::1234567890:user:requested_user_id',
                    'tenant_hint' => 'srn:dev:iam:na:1234567890:tenant',
                ]
            );

        $this->controller->preCheck($this->request, $this->application);
    }

    /**
     * @covers ::preCheck
     */
    public function testPreCheckWithoutConsent(): void
    {
        $token = new RememberMeToken(
            new UsernamePasswordToken(
                new User('test', 'user', []),
                '',
                Providers::PROVIDER_KEY_LOCAL
            )
        );

        $this->application->method('offsetGet')->with('config')->willReturn($this->config);

        $this->redirectUrlService->expects($this->once())
            ->method('getRedirectUrl')
            ->with($this->request)->willReturn('https://test.url');

        $this->session->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['tenant'], ['referer'])
            ->willReturnOnConsecutiveCalls(true, false);
        $this->session->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['tenant'], ['consent'])
            ->willReturnOnConsecutiveCalls('srn:dev:iam:na:1678464015:tenant', null);
        $this->session->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(['tenant', 'srn:dev:iam:na:1678464015:tenant'], ['referer', 'https://test.url']);

        $this->rememberMeService->expects($this->once())
            ->method('retrieve')
            ->willReturn($token);

        $this->assertNull($this->controller->preCheck($this->request, $this->application));
    }

    /**
     * @covers ::preCheck
     */
    public function testPreCheckWithConsent(): void
    {
        $token = new RememberMeToken(
            new UsernamePasswordToken(
                new User('test', 'user', []),
                '',
                Providers::PROVIDER_KEY_LOCAL
            )
        );

        $this->application->method('offsetGet')->with('config')->willReturn($this->config);

        $this->redirectUrlService->expects($this->never())->method('getRedirectUrl');
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('loginRender')
            ->willReturn('/login');

        $this->session->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(['tenant'], ['referer'])
            ->willReturnOnConsecutiveCalls(true, false);
        $this->session->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['tenant'], ['consent'])
            ->willReturnOnConsecutiveCalls('srn:dev:iam:na:1678464015:tenant', 'consent');
        $this->session->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(['tenant', 'srn:dev:iam:na:1678464015:tenant'], ['referer', '/login']);

        $this->rememberMeService->expects($this->once())
            ->method('retrieve')
            ->willReturn($token);

        $this->assertNull($this->controller->preCheck($this->request, $this->application));
    }

    /**
     * @covers ::showChangePasswordForm
     */
    public function testShowChangePasswordForm(): void
    {
        $this->application->method('getConfig')
            ->willReturn($this->config);

        $this->csrfManager->expects($this->once())
            ->method('getToken')
            ->with(CustomAssert\Csrf::FORM_TOKEN_ID)
            ->willReturn('secure-token');

        $this->session->expects($this->once())
            ->method('get')
            ->with('referer')
            ->willReturn('http://test.url');

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'password/change.html.twig',
                [
                    'csrf_token' => 'secure-token',
                    'passwordRequirements' => $this->config['local']['password_requirements'],
                    'redirectUrl' => 'http://test.url',
                ]
            )
            ->willReturn('html');
        $this->controller->showChangePasswordForm($this->application, $this->request);
    }

    /**
     * @covers ::showChangePasswordForm
     */
    public function testShowChangePasswordFormWithoutReferer(): void
    {
        $this->application->method('getConfig')
            ->willReturn($this->config);

        $this->csrfManager->expects($this->once())
            ->method('getToken')
            ->with(CustomAssert\Csrf::FORM_TOKEN_ID)
            ->willReturn('secure-token');

        $this->session->expects($this->once())
            ->method('get')
            ->with('referer')
            ->willReturn(null);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with('loginRender')
            ->willReturn('/login');

        $this->twig->expects($this->once())
            ->method('render')
            ->with(
                'password/change.html.twig',
                [
                    'csrf_token' => 'secure-token',
                    'passwordRequirements' => $this->config['local']['password_requirements'],
                    'redirectUrl' => '/login',
                ]
            )
            ->willReturn('html');
        $this->controller->showChangePasswordForm($this->application, $this->request);
    }

    /**
     * @covers ::changePasswordAction
     */
    public function testChangePasswordActionWithViolations()
    {
        $this->application->method('getConfig')
            ->willReturn($this->config);
        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())
            ->method('getMessage')
            ->willReturn('error test');

        $this->request->expects($this->exactly(4))
            ->method('get')
            ->withConsecutive(['oldPassword'], ['newPassword'], ['confirmPassword'], ['csrf_token'])
            ->willReturnOnConsecutiveCalls('old-password', 'new-password', 'confirm-password', 'csrf-token');

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->isType('array'), $this->isInstanceOf(Assert\Collection::class))
            ->willReturn(new \ArrayObject([$violation]));

        $this->flashBag->expects($this->once())
            ->method('set')
            ->with('error', 'error test');

        $this->csrfManager->expects($this->once())
            ->method('getToken')
            ->with(CustomAssert\Csrf::FORM_TOKEN_ID)
            ->willReturn('secure-token');

        $this->twig->expects($this->once())
            ->method('render')
            ->with('password/change.html.twig', $this->isType('array'))
            ->willReturn('html');

        $this->controller->changePasswordAction($this->application, $this->request);
    }

    /**
     * @covers ::changePasswordAction
     */
    public function testChangePasswordActionWrongPassword()
    {
        $this->application->method('getConfig')
            ->willReturn($this->config);

        $user = new User('test', 'user', [
            'password_hash' => 'test_password_hash',
        ]);
        $token = new RememberMeToken(
            new UsernamePasswordToken(
                $user,
                '',
                Providers::PROVIDER_KEY_LOCAL
            )
        );

        $this->request->expects($this->exactly(4))
            ->method('get')
            ->withConsecutive(['oldPassword'], ['newPassword'], ['confirmPassword'], ['csrf_token'])
            ->willReturnOnConsecutiveCalls('old-password', 'new-password', 'confirm-password', 'csrf-token');

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->isType('array'), $this->isInstanceOf(Assert\Collection::class))
            ->willReturn([]);

        $this->rememberMeService->expects($this->once())
            ->method('retrieve')
            ->willReturn($token);

        $this->encoderFactory->expects($this->once())
            ->method('getEncoder')
            ->with(User::class)
            ->willReturn($this->encoder);

        $this->encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with('test_password_hash', 'old-password', '')
            ->willReturn(false);

        $this->flashBag->expects($this->once())
            ->method('set')
            ->with('error', 'Old password is not valid');

        $this->csrfManager->expects($this->once())
            ->method('getToken')
            ->with(CustomAssert\Csrf::FORM_TOKEN_ID)
            ->willReturn('secure-token');

        $this->twig->expects($this->once())
            ->method('render')
            ->with('password/change.html.twig', $this->isType('array'))
            ->willReturn('html');

        $this->controller->changePasswordAction($this->application, $this->request);
    }

    /**
     * @covers ::changePasswordAction
     */
    public function testChangePasswordAction()
    {
        $this->config['grpc']['disabled'] = false;
        $this->application->method('getConfig')
            ->willReturn($this->config);

        $user = new User('test-user-id', '', [
            'id' => 'test-user-id',
            'password_hash' => 'test_password_hash',
        ]);
        $userUpdated = new User('test-user-id', '', [
            'id' => 'test-user-id',
            'password_hash' => 'encoded-new-password',
        ]);
        $token = new RememberMeToken(
            new UsernamePasswordToken($user, '', Providers::PROVIDER_KEY_LOCAL)
        );
        $token->setAttribute('tenantSrn', 'srn:dev:iam:na:1144464366:tenant');

        $this->request->expects($this->exactly(4))
            ->method('get')
            ->withConsecutive(['oldPassword'], ['newPassword'], ['confirmPassword'], ['csrf_token'])
            ->willReturnOnConsecutiveCalls('old-password', 'new-password', 'confirm-password', 'csrf-token');

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->isType('array'), $this->isInstanceOf(Assert\Collection::class))
            ->willReturn([]);

        $this->rememberMeService->expects($this->once())
            ->method('retrieve')
            ->willReturn($token);

        $this->encoderFactory->expects($this->once())
            ->method('getEncoder')
            ->with(User::class)
            ->willReturn($this->encoder);

        $this->encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with('test_password_hash', 'old-password', '')
            ->willReturn(true);

        $this->rememberMeService->expects($this->once())
            ->method('store')
            ->with($token);

        $this->application->expects($this->once())
            ->method('getSrnManager')
            ->willReturn(new Manager($this->config['idm']));

        /** @var UserAPIClient | \PHPUnit_Framework_MockObject_MockObject $userApi */
        $userApi = $this->createMock(UserAPIClient::class);
        $this->application->method('getGrpcUserApi')->willReturn($userApi);
        $this->application->method('offsetGet')->with('locale')->willReturn('en-US');

        $status = new \stdClass();
        $status->code = 0;
        $unaryCall = $this->createMock(UnaryCall::class);
        $userApi->expects($this->once())->method('SetPassword')
            ->willReturnCallback(function (SetPasswordRequest $request) use ($unaryCall) {
                $this->assertEquals('srn:dev:iam::1144464366:user:test-user-id', $request->getName());
                $this->assertEquals('new-password', $request->getPassword());
                $this->assertEmpty($request->getHash());
                $this->assertTrue($request->getSendEmail());
                $this->assertEquals('en-US', $request->getLocale());
                return $unaryCall;
            });

        $unaryCall->method('wait')->willReturn([null, $status]);

        $userProvider = $this->createMock(UserProviderInterface::class);
        $userProvider->method('refreshUser')
            ->willReturn($userUpdated);
        $userProviderBuilder = $this->createMock(UserProviderBuilder::class);
        $userProviderBuilder->method('build')
            ->willReturn($userProvider);
        $this->application->method('getUserProviderBuilder')
            ->willReturn($userProviderBuilder);

        $this->session->method('get')->with('referer')->willReturn('http://test.url');

        $this->twig->expects($this->once())
            ->method('render')
            ->with('password/success.change.html.twig', ['redirectUrl' => 'http://test.url'])
            ->willReturn('html');

        $this->controller->changePasswordAction($this->application, $this->request);
    }
}
