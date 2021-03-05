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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\MarketingExtras;

use Sugarcrm\IdentityProvider\App\Grpc\AppService;
use Sugarcrm\IdentityProvider\App\Mango;
use Sugarcrm\IdentityProvider\App\MarketingExtras\MarketingExtrasService;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\MarketingExtras\MarketingExtrasService
 */
class MarketingExtrasServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClient;

    /**
     * @var  ResponseInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $httpResponse;

    /**
     * @var LoggerInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var Mango\RestService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $restService;

    /**
     * @var string
     */
    private $crmUrl = 'https://test.crm.com:8080/crm';

    /**
     * @var string
     */
    private $urlFromCrm = 'https://marketing?domain=test.crm.com:8080&language=en_us&flavor=ULT&version=10.1.0&license';

    /**
     * @var string
     */
    private $tenant = 'srn:dev:iam:na:1077462458:tenant';

    /**
     * @var string
     */
    private $language = 'en-US';

    /**
     * @var array
     */
    private $config = [
        'baseUrl' => 'https://marketing',
        'timeoutMS' => 300,
        'connectTimeoutMS' => 150,
    ];

    /**
     * @var AppService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $grpcAppService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->grpcAppService = $this->createMock(AppService::class);
        $this->httpClient = $this->createMock(Client::class);
        $this->httpResponse = $this->createMock(ResponseInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->restService = $this->createMock(Mango\RestService::class);
    }

    /**
     * @covers ::getUrl
     * @covers ::getUrlByTenant
     * @covers ::jsonDecode
     */
    public function testGetUrlByTenantFromCrm()
    {
        $this->restService
            ->expects($this->once())
            ->method('get')
            ->with($this->crmUrl, ['login', 'marketingContentUrl'], ['selected_language' => 'en_us'])
            ->willReturn([\GuzzleHttp\json_encode($this->urlFromCrm), 'some-hash']);

        $this->grpcAppService
            ->expects($this->once())
            ->method('getCrmUrlByTenant')
            ->with($this->tenant)
            ->willReturn($this->crmUrl);

        $marketingExtras = new MarketingExtrasService(
            $this->config,
            $this->language,
            $this->grpcAppService,
            $this->restService,
            $this->logger
        );

        $this->assertEquals($this->urlFromCrm, $marketingExtras->getUrl($this->tenant));
    }

    /**
     * @covers ::getUrl
     * @covers ::getUrlByTenant
     * @covers ::isContentDisplayable
     * @covers ::jsonDecode
     */
    public function testGetUrlByTenantFromOldCrm()
    {
        $this->restService
            ->expects($this->once())
            ->method('get')
            ->with($this->crmUrl, ['login', 'marketingContentUrl'], ['selected_language' => 'en_us'])
            ->willReturn(['', '']);

        $this->grpcAppService
            ->expects($this->once())
            ->method('getCrmUrlByTenant')
            ->with($this->tenant)
            ->willReturn($this->crmUrl);

        $this->httpClient
            ->expects($this->once())
            ->method('__call')
            ->with('get')
            ->willReturn($this->httpResponse);

        /** @var MarketingExtrasService $marketingExtras */
        $marketingExtras = $this->getMockBuilder(MarketingExtrasService::class)
            ->setConstructorArgs(
                [$this->config, $this->language, $this->grpcAppService, $this->restService, $this->logger]
            )
            ->setMethods(['getHttpClient'])->getMock();
        $marketingExtras->method('getHttpClient')->willReturn($this->httpClient);

        $url = $marketingExtras->getUrl($this->tenant);
        $this->assertStringStartsWith(
            $this->config['baseUrl'],
            $url
        );
        $this->assertContains('language', $url);
        $this->assertContains('en_us', $url);
        $this->assertContains('domain', $url);
        $this->assertContains(urlencode('test.crm.com:8080'), $url);
    }

    /**
     * @covers ::getUrl
     * @covers ::getUrlByTenant
     * @covers ::isContentDisplayable
     * @covers ::getStaticUrl
     */
    public function testGetUrlByTenantFromOldCrmNotConnectToMarketing()
    {
        $this->restService
            ->expects($this->once())
            ->method('get')
            ->with($this->crmUrl, ['login', 'marketingContentUrl'], ['selected_language' => 'en_us'])
            ->willReturn(['', '']);

        $this->grpcAppService
            ->expects($this->once())
            ->method('getCrmUrlByTenant')
            ->with($this->tenant)
            ->willReturn($this->crmUrl);

        $this->httpClient
            ->expects($this->once())
            ->method('__call')
            ->with('get')
            ->willThrowException(new \Exception('Can not connect'));

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Could not get response from URL');

        /** @var MarketingExtrasService $marketingExtras */
        $marketingExtras = $this->getMockBuilder(MarketingExtrasService::class)
            ->setConstructorArgs(
                [$this->config, $this->language, $this->grpcAppService, $this->restService, $this->logger]
            )->setMethods(['getHttpClient'])->getMock();
        $marketingExtras->method('getHttpClient')->willReturn($this->httpClient);

        $this->assertEquals(MarketingExtrasService::STATIC_PATH, $marketingExtras->getUrl($this->tenant));
    }

    /**
     * @covers ::getUrl
     * @covers ::getUrlByTenant
     * @covers ::isContentDisplayable
     * @covers ::getStaticUrl
     */
    public function testGetUrlByTenantFromOldCrmNotShowIframe()
    {
        $this->restService
            ->expects($this->once())
            ->method('get')
            ->with($this->crmUrl, ['login', 'marketingContentUrl'], ['selected_language' => 'en_us'])
            ->willReturn(['', '']);

        $this->grpcAppService
            ->expects($this->once())
            ->method('getCrmUrlByTenant')
            ->with($this->tenant)
            ->willReturn($this->crmUrl);

        $this->httpClient
            ->expects($this->once())
            ->method('__call')
            ->with('get')
            ->willReturn($this->httpResponse);

        $this->httpResponse->method('getHeader')
            ->with('x-frame-options')
            ->willReturn('SAMEORIGIN');

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Cannot load iframe due to header from URL');

        /** @var MarketingExtrasService $marketingExtras */
        $marketingExtras = $this->getMockBuilder(MarketingExtrasService::class)
            ->setConstructorArgs([$this->config, $this->language, $this->grpcAppService, $this->restService, $this->logger])
            ->setMethods(['getHttpClient'])->getMock();
        $marketingExtras->method('getHttpClient')->willReturn($this->httpClient);

        $this->assertEquals(MarketingExtrasService::STATIC_PATH, $marketingExtras->getUrl($this->tenant));
    }

    /**
     * @covers ::getUrl
     * @covers ::isValidTenant
     * @covers ::getUrlWithoutTenant
     * @covers ::buildFullUrl
     */
    public function testGetUrlWithoutTenant()
    {
        $this->grpcAppService
            ->expects($this->never())
            ->method('getCrmUrlByTenant');

        $this->httpClient
            ->expects($this->once())
            ->method('__call')
            ->with('get')
            ->willReturn($this->httpResponse);

        /** @var MarketingExtrasService $marketingExtras */
        $marketingExtras = $this->getMockBuilder(MarketingExtrasService::class)
            ->setConstructorArgs(
                [$this->config, $this->language, $this->grpcAppService, $this->restService, $this->logger]
            )
            ->setMethods(['getHttpClient'])->getMock();
        $marketingExtras->method('getHttpClient')->willReturn($this->httpClient);


        $url = $marketingExtras->getUrl();
        $this->assertStringStartsWith(
            $this->config['baseUrl'],
            $url
        );
        $this->assertContains('language', $url);
        $this->assertContains('en_us', $url);
    }
}
