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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Grpc;

use Sugarcrm\Apis\Iam\App\V1alpha;

use Sugarcrm\IdentityProvider\App\Grpc\AppService;

use Grpc\UnaryCall;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Grpc\AppService
 */
class AppServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LoggerInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var V1alpha\AppAPIClient | \PHPUnit_Framework_MockObject_MockObject
     */
    private $appApi;

    /**
     * @var UnaryCall | \PHPUnit_Framework_MockObject_MockObject
     */
    private $unaryCall;

    /**
     * @var V1alpha\App
     */
    private $crmApp;

    /**
     * @var V1alpha\ListAppsResponse
     */
    private $grpcNotEmptyListResponse;

    /**
     * @var V1alpha\ListAppsResponse
     */
    private $grpcEmptyListResponse;

    /**
     * @var string
     */
    private $crmUrl = 'https://test.crm.com:8080/crm';

    /**
     * @var string
     */
    private $crmUrlRedirectUris = 'https://test.crm.com:8080/crm?some=query';

    /**
     * @var \stdClass
     */
    private $statusOk;

    /**
     * @var \stdClass
     */
    private $statusErr;

    /**
     * @var string
     */
    private $tenant = 'srn:dev:iam:na:1077462458:tenant';

    /**
     * @var AppService
     */
    private $appService;

    protected function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->appApi = $this->createMock(V1alpha\AppAPIClient::class);
        $this->unaryCall = $this->createMock(UnaryCall::class);

        $this->crmApp = new V1alpha\App();
        $this->crmApp->setRedirectUris([$this->crmUrlRedirectUris]);
        $this->grpcNotEmptyListResponse = new V1alpha\ListAppsResponse();
        $this->grpcNotEmptyListResponse->setApps([$this->crmApp]);

        $this->grpcEmptyListResponse = new V1alpha\ListAppsResponse();
        $this->grpcEmptyListResponse->setApps([]);

        $this->statusOk = new \stdClass();
        $this->statusOk->code = \GRPC\CALL_OK;
        $this->statusErr = new \stdClass();
        $this->statusErr->code = \GRPC\CALL_ERROR;

        $this->appService = new AppService(false, $this->appApi, $this->logger);
    }

    /**
     * @covers  ::getCrmUrlByTenant
     */
    public function testGetCrmUrlByTenant()
    {
        $this->appApi
            ->expects($this->once())
            ->method('ListApps')
            ->willReturn($this->unaryCall);
        $this->unaryCall
            ->method('wait')
            ->willReturn([$this->grpcNotEmptyListResponse, $this->statusOk]);
        $this->logger
            ->expects($this->never())
            ->method('warning');

        $this->assertEquals($this->crmUrl, $this->appService->getCrmUrlByTenant($this->tenant));
    }

    /**
     * @covers  ::getCrmUrlByTenant
     */
    public function testGetCrmUrlByTenantByError()
    {
        $this->appApi
            ->expects($this->once())
            ->method('ListApps')
            ->willReturn($this->unaryCall);

        $this->unaryCall
            ->method('wait')
            ->willReturn([$this->grpcNotEmptyListResponse, $this->statusErr]);

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with($this->stringStartsWith('Wrong answer from APP API, code:'));

        $this->assertNull($this->appService->getCrmUrlByTenant($this->tenant));
    }

    /**
     * @covers  ::getCrmUrlByTenant
     */
    public function testGetCrmUrlByTenantByNoCRM()
    {
        $this->appApi
            ->expects($this->once())
            ->method('ListApps')
            ->willReturn($this->unaryCall);

        $this->unaryCall
            ->method('wait')
            ->willReturn([$this->grpcEmptyListResponse, $this->statusOk]);

        $this->logger
            ->expects($this->never())
            ->method('warning');

        $this->assertNull($this->appService->getCrmUrlByTenant($this->tenant));
    }

    /**
     * @covers  ::getCrmUrlByTenant
     */
    public function testGetCrmUrlByTenantByDisabledGPRS()
    {
        $this->appApi
            ->expects($this->never())
            ->method('ListApps');

        $this->logger
            ->expects($this->never())
            ->method('warning');

        $appService = new AppService(true, $this->appApi, $this->logger);

        $this->assertNull($appService->getCrmUrlByTenant($this->tenant));
    }

    /**
     * @covers ::getTenantApplicationsDomains
     */
    public function testGetTenantApplicationsDomainsDisabledGRPS(): void
    {
        $this->appApi
            ->expects($this->never())
            ->method('ListApps');

        $appService = new AppService(true, $this->appApi, $this->logger);

        $this->assertEquals([], $appService->getTenantApplicationsDomains($this->tenant));
    }

    /**
     * @covers ::getTenantApplicationsDomains
     */
    public function testGetTenantApplicationsDomains(): void
    {
        $app = new V1alpha\App();
        $app->setRedirectUris(['http://test.com/callback', 'https://test-new.com/callback', 'https://test.com/callback']);

        $app1 = new V1alpha\App();
        $app1->setRedirectUris(['http://test1.com/callback?a=1']);

        $listResponse = new V1alpha\ListAppsResponse();
        $listResponse->setApps([$app, $app1]);

        $this->appApi
            ->expects($this->once())
            ->method('ListApps')
            ->willReturn($this->unaryCall);

        $this->unaryCall
            ->method('wait')
            ->willReturn([$listResponse, $this->statusOk]);

        $this->assertEquals(
            ['test.com', 'test-new.com', 'test1.com'],
            $this->appService->getTenantApplicationsDomains($this->tenant)
        );
    }
}
