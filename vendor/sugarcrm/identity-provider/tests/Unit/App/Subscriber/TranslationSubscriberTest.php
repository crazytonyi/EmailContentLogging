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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Listener\Success;

use Closure;
use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Authentication\CookieService;
use Sugarcrm\IdentityProvider\App\Mango\MetadataService;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\App\Subscriber\TranslationSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Subscriber\TranslationSubscriber
 */
class TranslationSubscriberTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject | Response
     */
    protected $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ParameterBag
     */
    protected $requestQuery;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ParameterBag
     */
    protected $requestCookies;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | ResponseHeaderBag
     */
    protected $responseHeaders;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | GetResponseEvent
     */
    protected $getResponseEvent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | FilterResponseEvent
     */
    protected $filterResponseEvent;

    /**
     * @var CookieService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieService;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var MetadataService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataService;

    /**
     * @var string
     */
    private $paramName = 'locale';

    /**
     * @var TranslationSubscriber
     */
    private $subscriber;

    protected function setUp()
    {
        $this->cookieService = $this->createMock(CookieService::class);
        $this->session = $this->createMock(Session::class);
        $this->metadataService = $this->createMock(MetadataService::class);

        $this->application =  $this->createMock(Application::class);
        $this->application->method('getCookieService')->willReturn($this->cookieService);
        $this->application->method('getSession')->willReturn($this->session);
        $this->application->method('getMetadataService')->willReturn($this->metadataService);

        $this->request = $this->createMock(Request::class);

        $this->requestQuery = $this->createMock(ParameterBag::class);
        $this->request->query = $this->requestQuery;

        $this->requestCookies = $this->createMock(ParameterBag::class);
        $this->request->cookies = $this->requestCookies;

        $this->getResponseEvent = $this->createMock(GetResponseEvent::class);
        $this->getResponseEvent->expects($this->any())->method('getRequest')->willReturn($this->request);

        $this->responseHeaders = $this->createMock(ResponseHeaderBag::class);

        $this->response = $this->createMock(Response::class);
        $this->response->headers = $this->responseHeaders;

        $this->filterResponseEvent = $this->createMock(FilterResponseEvent::class);
        $this->filterResponseEvent->expects($this->any())->method('getResponse')->willReturn($this->response);
        $this->filterResponseEvent->expects($this->any())->method('getRequest')->willReturn($this->request);

        $this->subscriber = new TranslationSubscriber($this->application, $this->paramName);

        parent::setUp();
    }

    /**
     * @covers ::onKernelRequest
     */
    public function testOnKernelRequestDefaultLocale()
    {
        $this->application->method('offsetGet')->willReturnMap([
            ['locale', 'en-US'],
        ]);

        $this->requestQuery->expects($this->once())
            ->method('has')
            ->with($this->paramName)
            ->willReturn(false);
        $this->cookieService->expects($this->atLeastOnce())
            ->method('getLocaleCookie')
            ->with($this->request)
            ->willReturn('');

        $this->application
            ->expects($this->exactly(2))
            ->method('offsetSet')
            ->withConsecutive(['selectedUserLocale', null], ['locale', 'en-US']);

        $subscriber = new TranslationSubscriber($this->application, $this->paramName);
        $subscriber->onKernelRequest($this->getResponseEvent);
    }

    /**
     * @covers ::onKernelRequest
     */
    public function testOnKernelRequestQueryLocale()
    {
        $this->requestQuery->expects($this->once())
            ->method('has')
            ->with($this->paramName)
            ->willReturn(true);
        $this->requestQuery->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([$this->paramName], [TenantConfigInitializer::REQUEST_KEY])
            ->willReturnOnConsecutiveCalls('de-DE', null);
        $this->cookieService->expects($this->never())
            ->method('getLocaleCookie');

        $this->application
            ->expects($this->exactly(3))
            ->method('offsetSet')
            ->withConsecutive(['selectedUserLocale', null], ['selectedUserLocale', 'de-DE'], ['locale', 'de-DE']);

        $this->subscriber->onKernelRequest($this->getResponseEvent);
    }

    /**
     * @covers ::onKernelRequest
     */
    public function testOnKernelRequestCookieLocale()
    {
        $this->requestQuery->expects($this->once())
            ->method('has')
            ->with($this->paramName)
            ->willReturn(false);

        $this->cookieService->expects($this->atLeastOnce())
            ->method('getLocaleCookie')
            ->with($this->request)
            ->willReturn('fr-FR');

        $this->application
            ->expects($this->exactly(3))
            ->method('offsetSet')
            ->withConsecutive(['selectedUserLocale', null], ['selectedUserLocale', 'fr-FR'], ['locale', 'fr-FR']);

        $this->subscriber->onKernelRequest($this->getResponseEvent);
    }

    /**
     * @covers ::onKernelResponse
     */
    public function testOnKernelResponseWithoutSelectedUserLocale(): void
    {
        $this->application->method('offsetGet')->willReturnMap([
            ['locale', 'en-US'],
        ]);

        $this->cookieService->expects($this->never())->method('setLocaleCookie');
        $this->cookieService->expects($this->once())
            ->method('setUICookie')
            ->with($this->response, 'en-US');
        $this->subscriber->onKernelResponse($this->filterResponseEvent);
    }

    /**
     * @covers ::onKernelResponse
     */
    public function testOnKernelResponseWithSelectedUserLocale(): void
    {
        $cookieService = $this->createMock(CookieService::class);

        $application =  $this->createMock(Application::class);
        $application->method('getCookieService')->willReturn($cookieService);

        $subscriber = new TranslationSubscriber($application, $this->paramName);

        $application->method('offsetGet')
            ->withConsecutive(['selectedUserLocale'], ['selectedUserLocale'], ['locale'])
            ->willReturnOnConsecutiveCalls('de-DE', 'de-DE', 'de-UI');
        $cookieService->expects($this->once())
            ->method('setLocaleCookie')
            ->with($this->response, 'de-DE');
        $cookieService->expects($this->once())
            ->method('setUICookie')
            ->with($this->response, 'de-UI');
        $subscriber->onKernelResponse($this->filterResponseEvent);
    }

    /**
     * Provides data for testOnKernelRequestWithLanguagesMetadata
     * @return array
     */
    public function onKernelRequestWithLanguagesMetadataProvider(): array
    {
        return [
            'noTenantInSession' => [
                'tenant' => null,
                'tenantHint' => null,
                'selectedUserLocale' => '',
                'defaultLanguage' => 'de-DE',
                'expectedTenant' => null,
                'languagesList' => [
                    'en-US' => true,
                    'de-DE' => true,
                ],
                'expectedAssertion' => static function (TranslationSubscriberTest $context) {
                    $context->application->expects($context->exactly(2))
                        ->method('offsetSet')
                        ->withConsecutive(['selectedUserLocale', null], ['locale', 'en-US']);
                }
            ],
            'tenantInSessionUserLocaleIsSetAndNotInLanguagesList' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'tenantHint' => null,
                'selectedUserLocale' => 'fr-FR',
                'defaultLanguage' => 'de-DE',
                'expectedTenant' => 'srn:dev:iam:na:1396472465:tenant',
                'languagesList' => [
                    'en-US' => true,
                    'de-DE' => true,
                ],
                'expectedAssertion' => static function (TranslationSubscriberTest $context) {
                    $context->application->expects($context->exactly(4))
                        ->method('offsetSet')
                        ->withConsecutive(
                            ['selectedUserLocale', null],
                            ['selectedUserLocale', 'fr-FR'],
                            ['selectedUserLocale', 'de-DE'],
                            ['locale', 'de-DE']
                        );
                }
            ],
            'tenantInSessionUserLocaleIsSetAndInLanguagesList' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'tenantHint' => null,
                'selectedUserLocale' => 'fr-FR',
                'defaultLanguage' => 'de-DE',
                'expectedTenant' => 'srn:dev:iam:na:1396472465:tenant',
                'languagesList' => [
                    'en-US' => true,
                    'fr-FR' => true,
                    'de-DE' => true,
                ],
                'expectedAssertion' => static function (TranslationSubscriberTest $context) {
                    $context->application->expects($context->exactly(3))
                        ->method('offsetSet')
                        ->withConsecutive(
                            ['selectedUserLocale', null],
                            ['selectedUserLocale', 'fr-FR'],
                            ['locale', 'fr-FR']
                        );
                }
            ],
            'tenantInSessionUserLocaleNotSetNoDefaultLanguage' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'tenantHint' => null,
                'selectedUserLocale' => '',
                'defaultLanguage' => '',
                'expectedTenant' => 'srn:dev:iam:na:1396472465:tenant',
                'languagesList' => [
                    'en-US' => true,
                    'de-DE' => true,
                ],
                'expectedAssertion' => static function (TranslationSubscriberTest $context) {
                    $context->application->expects($context->exactly(2))
                        ->method('offsetSet')
                        ->withConsecutive(
                            ['selectedUserLocale', null],
                            ['locale', 'en-US']
                        );
                }
            ],
            'noTenantInSessionTenantHintIsPresentNoSelectedLocale' => [
                'tenant' => null,
                'tenantHint' => 'srn:dev:iam:na:1396472465:tenant',
                'selectedUserLocale' => '',
                'defaultLanguage' => 'de-DE',
                'expectedTenant' => 'srn:dev:iam:na:1396472465:tenant',
                'languagesList' => [
                    'en-US' => true,
                    'de-DE' => true,
                ],
                'expectedAssertion' => static function (TranslationSubscriberTest $context) {
                    $context->application->expects($context->exactly(2))
                        ->method('offsetSet')
                        ->withConsecutive(
                            ['selectedUserLocale', null],
                            ['locale', 'de-DE']
                        );
                }
            ],
        ];
    }

    /**
     * @param string|null $tenant
     * @param string|null $tenantHint
     * @param string|null $selectedUserLocale
     * @param string|null $defaultLanguage
     * @param string|null $expectedTenant
     * @param array $languagesList
     * @param Closure $expectedAssertion
     *
     * @covers ::onKernelRequest
     * @dataProvider onKernelRequestWithLanguagesMetadataProvider
     */
    public function testOnKernelRequestWithLanguagesMetadata(
        ?string $tenant,
        ?string $tenantHint,
        ?string $selectedUserLocale,
        ?string $defaultLanguage,
        ?string $expectedTenant,
        array $languagesList,
        Closure $expectedAssertion
    ): void {
        $this->application->method('offsetGet')->willReturnMap([
            ['locale', 'en-US'],
            ['selectedUserLocale', $selectedUserLocale],
        ]);

        $this->requestQuery->method('get')
            ->with(TenantConfigInitializer::REQUEST_KEY)
            ->willReturn($tenantHint);
        $this->cookieService->method('getLocaleCookie')->willReturn($selectedUserLocale);

        $this->session->method('get')->with('tenant', null)->willReturn($tenant);
        $this->metadataService->method('getDefaultLanguage')
            ->with($expectedTenant)
            ->willReturn($defaultLanguage);
        $this->metadataService->method('getLanguages')
            ->with($expectedTenant)
            ->willReturn($languagesList);

        $expectedAssertion($this);

        $subscriber = new TranslationSubscriber($this->application, $this->paramName);
        $subscriber->onKernelRequest($this->getResponseEvent);
    }
}
