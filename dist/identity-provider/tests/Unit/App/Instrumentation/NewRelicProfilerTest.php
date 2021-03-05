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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Instrumentation;

use Psr\Log\LoggerInterface;
use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Instrumentation\NewRelicProfiler;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Instrumentation\NewRelicProfiler
 */
class NewRelicProfilerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $application;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->application = $this->createMock(Application::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->application->method('getLogger')->willReturn($this->logger);
    }

    /**
     * @covers ::__construct
     */
    public function testDisabled()
    {
        $this->config['newrelic']['enabled'] = 0;
        $this->application->method('getConfig')->willReturn($this->config);

        $profiler = $this->getMockBuilder(NewRelicProfiler::class)
            ->disableOriginalConstructor()
            ->setMethods(['isExtensionLoaded'])
            ->getMock();

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Created New Relic profiler', ['enabled' => false]);

        $reflectedClass = new \ReflectionClass(NewRelicProfiler::class);
        $reflectedClass->getConstructor()->invoke($profiler, $this->application);
    }

    /**
     * @covers ::__construct
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Profiling enabled but no New Relic extension found, please install
     */
    public function testNoExtensionLoadedWhenEnabled()
    {
        $this->config['newrelic']['enabled'] = true;
        $this->application->method('getConfig')->willReturn($this->config);

        $profiler = $this->getMockBuilder(NewRelicProfiler::class)
            ->disableOriginalConstructor()
            ->setMethods(['isExtensionLoaded'])
            ->getMock();

        $profiler->expects($this->once())
            ->method('isExtensionLoaded')
            ->willReturn(false);

        $reflectedClass = new \ReflectionClass(NewRelicProfiler::class);
        $reflectedClass->getConstructor()->invoke($profiler, $this->application);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Profiling enabled but no New Relic license was found
     */
    public function testNoLicenseWhenEnabled()
    {
        $this->config['newrelic']['enabled'] = true;
        $this->application->method('getConfig')->willReturn($this->config);

        $profiler = $this->getMockBuilder(NewRelicProfiler::class)
            ->disableOriginalConstructor()
            ->setMethods(['isExtensionLoaded'])
            ->getMock();

        $profiler->expects($this->once())
            ->method('isExtensionLoaded')
            ->willReturn(true);

        $reflectedClass = new \ReflectionClass(NewRelicProfiler::class);
        $reflectedClass->getConstructor()->invoke($profiler, $this->application);
    }

    /**
     *
     * @return array
     */
    public function enabledDataProvider(): array
    {
        return [
            'bool enabled' => [
                'isEnabled' => true,
                'expectEnabled' => true,
            ],
            'bool disabled' => [
                'isEnabled' => false,
                'expectEnabled' => false,
            ],
            'str enabled' => [
                'isEnabled' => 'aaaa',
                'expectEnabled' => true,
            ],
            'str disabled' => [
                'isEnabled' => '',
                'expectEnabled' => false,
            ],
            'str flag enabled' => [
                'isEnabled' => '1',
                'expectEnabled' => true,
            ],
            'str flag disabled' => [
                'isEnabled' => '0',
                'expectEnabled' => false,
            ],
            'flag enabled' => [
                'isEnabled' => 1,
                'expectEnabled' => true,
            ],
            'flag disabled' => [
                'isEnabled' => 0,
                'expectEnabled' => false,
            ],
        ];
    }

    /**
     * @covers ::isEnabled
     * @dataProvider enabledDataProvider
     *
     * @param $isEnabled
     * @param $expectEnabled
     */
    public function testEnabled($isEnabled, $expectEnabled): void
    {
        $this->config['newrelic']['enabled'] = $isEnabled;
        $this->config['newrelic']['license'] = 'asdf';
        $this->application->method('getConfig')->willReturn($this->config);

        $profiler = $this->getMockBuilder(NewRelicProfiler::class)
            ->disableOriginalConstructor()
            ->setMethods(['isExtensionLoaded'])
            ->getMock();

        $profiler->method('isExtensionLoaded')->willReturn(true);

        $reflectedClass = new \ReflectionClass(NewRelicProfiler::class);
        $reflectedClass->getConstructor()->invoke($profiler, $this->application);

        $this->assertEquals($expectEnabled, $profiler->isEnabled());
    }

    /**
     *
     * @return array
     */
    public function startDataProvider(): array
    {
        return [
            'enabled' => [
                'isEnabled' => true,
                'calledTimes' => 1,
            ],
            'disabled' => [
                'isEnabled' => false,
                'calledTimes' => 0,
            ],
        ];
    }

    /**
     * @covers ::start
     * @dataProvider startDataProvider
     *
     * @param $isEnabled
     * @param $calledTimes
     */
    public function testStart($isEnabled, $calledTimes): void
    {
        $profiler = $this->getMockBuilder(NewRelicProfiler::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled', 'startTransaction'])
            ->getMock();
        $profiler->method('isEnabled')->willReturn($isEnabled);

        $profiler->expects($this->exactly($calledTimes))
            ->method('startTransaction');

        $profiler->start();
    }

    /**
     *
     * @return array
     */
    public function stopDataProvider(): array
    {
        return [
            'enabled' => [
                'isEnabled' => true,
                'calledTimes' => 1,
            ],
            'disabled' => [
                'isEnabled' => false,
                'calledTimes' => 0,
            ],
        ];
    }

    /**
     * @covers ::stop
     * @dataProvider stopDataProvider
     *
     * @param $isEnabled
     * @param $calledTimes
     */
    public function testStop($isEnabled, $calledTimes): void
    {
        $profiler = $this->getMockBuilder(NewRelicProfiler::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled', 'endTransaction'])
            ->getMock();
        $profiler->method('isEnabled')->willReturn($isEnabled);

        $profiler->expects($this->exactly($calledTimes))
            ->method('endTransaction')->willReturn(true);

        $profiler->stop();
    }
}
