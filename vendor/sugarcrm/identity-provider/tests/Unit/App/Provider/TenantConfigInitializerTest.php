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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Provider;

use Psr\Log\LoggerInterface;

use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Authentication\RememberMe\Service as RememberMeService;
use Sugarcrm\IdentityProvider\App\Repository\Exception\TenantInDifferentRegionException;
use Sugarcrm\IdentityProvider\App\Regions\TenantRegion;
use Sugarcrm\IdentityProvider\App\Repository\TenantRepository;
use Sugarcrm\IdentityProvider\App\TenantConfiguration;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Sugarcrm\IdentityProvider\Authentication\Tenant;
use Sugarcrm\IdentityProvider\Srn;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class TenantConfigInitializerTest
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer
 */
class TenantConfigInitializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application | \PHPUnit_Framework_MockObject_MockObject
     */
    private $application;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionService;

    /**
     * @var TenantRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    private $tenantRepository;

    /**
     * @var Request | \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var TenantConfigInitializer
     */
    private $initializer;

    /**
     * @var TenantConfiguration | \PHPUnit_Framework_MockObject_MockObject
     */
    private $tenantConfiguration;

    /**
     * @var RememberMeService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $rememberMeService;

    /**
     * @var TenantRegion | \PHPUnit_Framework_MockObject_MockObject
     */
    private $tenantRegion;

    /**
     * @var Srn\Manager
     */
    private $srnManager;

    /**
     * @var Srn\Srn
     */
    private $createdSrn;

    /**
     * @var array
     */
    private $baseConfigArray = ['base', 'idm', 'config'];

    /**
     * @var array
     */
    private $tenantConfigArray = ['base', 'idm', 'merged', 'with', 'tenant', 'config'];

    /**
     * @var array
     */
    private $config = [
        'idm' => [
            'region' => 'na',
        ],
    ];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->application = $this->createMock(Application::class);

        $this->application->method('getConfig')->willReturn($this->config);

        $this->tenantRegion = $this->createMock(TenantRegion::class);
        $this->application->method('getTenantRegion')->willReturn($this->tenantRegion);

        $this->rememberMeService = $this->createMock(RememberMeService::class);
        $this->application->method('getRememberMeService')->willReturn($this->rememberMeService);

        $this->tenantConfiguration = $this->createMock(TenantConfiguration::class);
        $this->application->method('getTenantConfiguration')->willReturn($this->tenantConfiguration);
        $this->application->method('getLogger')->willReturn($this->logger);

        $this->sessionService = $this->createMock(Session::class);
        $this->request = $this->createMock(Request::class);
        $this->request->request = $this->createMock(ParameterBag::class);
        $this->request->cookies = $this->createMock(ParameterBag::class);
        $this->request->headers = $this->createMock(HeaderBag::class);
        $this->request->attributes = $this->createMock(ParameterBag::class);
        $this->request->attributes->method('get')->willReturn('someRoute');

        $this->tenantRepository = $this->createMock(TenantRepository::class);
        $this->application->expects($this->any())->method('getTenantRepository')->willReturn($this->tenantRepository);

        $this->createdSrn = $this->createMock(Srn\Srn::class);
        $this->createdSrn->method('getPartition')
            ->willReturn('createdPartition');
        $this->createdSrn->method('getService')
            ->willReturn('createdService');
        $this->createdSrn->method('getResource')
            ->willReturn(['createdResource']);
        $this->srnManager = $this->createMock(Srn\Manager::class);
        $this->srnManager->method('createTenantSrn')
            ->willReturn($this->createdSrn);

        $this->request->method('getSession')
            ->willReturn($this->sessionService);

        $this->application->method('offsetGet')
            ->willReturnMap([
                ['config', $this->baseConfigArray],
            ]);
        $this->application
            ->method('getSrnManager')
            ->willReturn($this->srnManager);
        $this->initializer = new TenantConfigInitializer($this->application);
    }


    /**
     * @see testPriorityOfTenantSources
     * @return array
     */
    public function priorityOfTenantSourcesDP()
    {
        $tenants = [
            'session' => 'srn:session:tenant:eu:1000000001:tenant',
            'cookie' => 'srn:session:tenant:eu:2000000002:tenant',
            'requestLoginHintId' => [
                ['tenant_hint', null, '1000000003'],
                ['tid', null, ''],
            ],
            'requestLoginHintShortId' => [
                ['tenant_hint', null, '1000000003'],
                ['tid', null, ''],
            ],
            'requestTid' => [
                ['tid', null, 'srn:request:tenant:eu:1000000003:tenant'],
                ['tenant_hint', null, ''],
            ],
            'requestLoginHint' => [
                ['tid', null, ''],
                ['tenant_hint', null, 'srn:request:tenant:eu:1000000004:tenant'],
            ],
            'requestBoth' => [
                ['tid', null, 'srn:request:tenant:eu:1000000003:tenant'],
                ['tenant_hint', null, 'srn:request:tenant:eu:1000000004:tenant'],
            ],
        ];

        $tokenWithTenant = $this->createMock(RememberMeToken::class);
        $tokenWithTenant->method('hasAttribute')
            ->will($this->returnValueMap([
                ['tenantSrn', true],
            ]));
        $tokenWithTenant->method('getAttribute')
            ->will($this->returnValueMap([
                    ['tenantSrn', 'srn:request:tenant:eu:1000000004:tenant'],
                ]));

        return [
            'tenant from session' => [
                'input' => [
                    'session' => [
                        'has' => true,
                        'tenant' => $tenants['session'],
                    ],
                    'cookie' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => [],
                    'token' => null,
                ],
                'tenantId' => '1000000001',
                'expectedTenant' =>
                    [
                        'string' => $tenants['session'],
                        'object' => Srn\Converter::fromString($tenants['session'])
                    ],
            ],
            'tenant from cookie' => [
                'input' => [
                    'session' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'cookie' => [
                        'has' => true,
                        'tenant' => $tenants['cookie'],
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => [],
                    'token' => null,
                ],
                'tenantId' => '2000000002',
                'expectedTenant' =>
                    [
                        'string' => $tenants['cookie'],
                        'object' => Srn\Converter::fromString($tenants['cookie'])
                    ],
            ],
            'tenant from request login hint' => [
                'input' => [
                    'session' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'cookie' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => $tenants['requestLoginHint'],
                    'token' => null,
                ],
                'tenantId' => '1000000003',
                'expectedTenant' => [
                    'string' => 'srn:request:tenant:eu:1000000004:tenant',
                    'object' => Srn\Converter::fromString('srn:request:tenant:eu:1000000004:tenant')
                ],
            ],
            'tenant id from request login hint' => [
                'input' => [
                    'session' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'cookie' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => $tenants['requestLoginHintId'],
                    'token' => null,
                ],
                'tenantId' => '1000000003',
                'expectedTenant' => [
                    'string' => 'srn:createdPartition:createdService::1000000003:createdResource',
                    'object' => Srn\Converter::fromString('srn:createdPartition:createdService::1000000003:createdResource'),
                ],
            ],
            'tenant short id from request login hint' => [
                'input' => [
                    'session' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'cookie' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => $tenants['requestLoginHintShortId'],
                    'token' => null,
                ],
                'tenantId' => '1000000003',
                'expectedTenant' => [
                    'string' => 'srn:createdPartition:createdService::1000000003:createdResource',
                    'object' => Srn\Converter::fromString('srn:createdPartition:createdService::1000000003:createdResource'),
                ],
            ],
            'tenant from request tid' => [
                'input' => [
                    'session' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'cookie' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => $tenants['requestTid'],
                    'token' => null,
                ],
                'tenantId' => '1000000004',
                'expectedTenant' => [
                    'string' => 'srn:request:tenant:eu:1000000003:tenant',
                    'object' => Srn\Converter::fromString('srn:request:tenant:eu:1000000003:tenant')
                ],
            ],
            'tenant from request both' => [
                'input' => [
                    'session' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'cookie' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => $tenants['requestBoth'],
                    'token' => null,
                ],
                'tenantId' => '1000000003',
                'expectedTenant' => [
                    'string' => 'srn:request:tenant:eu:1000000003:tenant',
                    'object' => Srn\Converter::fromString('srn:request:tenant:eu:1000000003:tenant')
                ],
            ],
            'tenant from authorized user' => [
                'input' => [
                    'session' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'cookie' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => [],
                    'token' => $tokenWithTenant,
                ],
                'tenantId' => '1000000004',
                'expectedTenant' => [
                    'string' => 'srn:request:tenant:eu:1000000004:tenant',
                    'object' => Srn\Converter::fromString('srn:request:tenant:eu:1000000004:tenant')
                ],
            ],
            'tenant from authorized user and session' => [
                'input' => [
                    'session' => [
                        'has' => true,
                        'tenant' => $tenants['session'],
                    ],
                    'cookie' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => [],
                    'token' => $tokenWithTenant,
                ],
                'tenantId' => '1000000001',
                'expectedTenant' =>
                    [
                        'string' => $tenants['session'],
                        'object' => Srn\Converter::fromString($tenants['session'])
                    ],
            ],
            'tenant from authorized user and cookie' => [
                'input' => [
                    'session' => [
                        'has' => false,
                        'tenant' => null,
                    ],
                    'cookie' => [
                        'has' => true,
                        'tenant' => $tenants['cookie'],
                    ],
                    'jwtTenant' => null,
                    'requestTenant' => [],
                    'token' => $tokenWithTenant,
                ],
                'tenantId' => '1000000004',
                'expectedTenant' =>
                    [
                        'string' => 'srn:request:tenant:eu:1000000004:tenant',
                        'object' => Srn\Converter::fromString('srn:request:tenant:eu:1000000004:tenant'),
                    ],
            ],
        ];
    }

    /**
     * Testing priority of tenant sources and valid cases.
     * @covers ::__invoke
     * @dataProvider priorityOfTenantSourcesDP
     * @param array $input
     * @param string $tenantId
     * @param array $expectedTenant
     */
    public function testPriorityOfTenantSources(array $input, $tenantId, array $expectedTenant)
    {
        $this->sessionService->method('has')->willReturnMap(
            [
                ['tenant', $input['session']['has']],
            ]
        );
        $this->sessionService->method('get')->willReturnMap(
            [
                ['tenant', null, $input['session']['tenant']],
            ]
        );
        $this->request->cookies->method('has')->willReturnMap(
            [
                ['samlTid', $input['cookie']['has']],
            ]
        );
        $this->request->cookies->method('get')->willReturnMap(
            [
                ['samlTid', null, $input['cookie']['tenant']],
            ]
        );
        $this->request
            ->method('get')
            ->willReturnMap($input['requestTenant']);

        $this->createdSrn->method('getTenantId')
            ->willReturn($tenantId);
        $this->tenantConfiguration
            ->expects($this->once())
            ->method('merge')
            ->with(
                $this->equalTo($expectedTenant['object']),
                $this->equalTo($this->baseConfigArray)
            )
            ->willReturn($this->tenantConfigArray);
        $this->application
            ->expects($this->once())
            ->method('offsetSet')
            ->with(
                $this->equalTo('config'),
                $this->equalTo($this->tenantConfigArray)
            );
        $this->sessionService
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo('tenant'),
                $this->equalTo($expectedTenant['string'])
            );

        $this->tenantRepository->expects($this->any())
            ->method('findTenantById')
            ->with($tenantId)
            ->willReturn(Tenant::new($tenantId, 'na'));

        $this->rememberMeService->method('retrieve')
            ->willReturn($input['token']);

        call_user_func($this->initializer, $this->request);
    }

    /**
     * Testing if no tenant in any source.
     * @covers ::__invoke
     * @expectedException \RuntimeException
     */
    public function testNoTenant()
    {
        $this->sessionService->method('has')->willReturn(false);
        $this->sessionService->expects($this->never())->method('get');
        $this->sessionService
            ->expects($this->never())
            ->method('set');
        $this->tenantConfiguration
            ->expects($this->never())
            ->method('merge');
        $this->application
            ->expects($this->never())
            ->method('offsetSet')
            ->with(
                $this->equalTo('config'),
                $this->equalTo($this->tenantConfigArray)
            );
        $this->request
            ->method('get')
            ->willReturnMap([['tid', null, null], ['tenant_hint', null, null]]);

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with('Cant build configs without tenant id');

        $this->rememberMeService->method('retrieve')
            ->willReturn(null);

        call_user_func($this->initializer, $this->request);
    }

    /**
     * @see testTenantInDifferentRegion
     * @return array
     */
    public function getTenantsInDifferentRegion(): array
    {
        return [
            ['tenantRegion' => 'eu', 'tenantId' => '2000000001', 'tenantString' => '2000000001'],
            ['tenantRegion' => 'eu', 'tenantId' => '2000000002', 'tenantString' => 'srn:dev:iam:eu:2000000002:tenant'],
            ['tenantRegion' => 'eu', 'tenantId' => '2000000003', 'tenantString' => 'srn:dev:iam:na:2000000003:tenant'],
        ];
    }

    /**
     * @covers ::checkTenantRegion
     * @dataProvider getTenantsInDifferentRegion
     * @param string $tenantRegion
     * @param string $tenantId
     */
    public function testTenantInDifferentRegion(string $tenantRegion, string $tenantId, string $tenantString)
    {
        $this->request
            ->method('get')
            ->willReturnMap([[TenantConfigInitializer::REQUEST_KEY, null, $tenantString]]);

        $this->tenantRegion
            ->method('getRegion')
            ->with($this->equalTo($tenantId))
            ->willReturn($tenantRegion);

        try {
            call_user_func($this->initializer, $this->request);
            $this->fail();
        } catch (TenantInDifferentRegionException $exception) {
            $this->assertEquals($tenantId, $exception->getTenantId());
            $this->assertEquals($tenantRegion, $exception->getTenantRegion());
        }
    }
}
