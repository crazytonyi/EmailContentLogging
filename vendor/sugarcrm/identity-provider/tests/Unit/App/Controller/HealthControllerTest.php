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

use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Controller\HealthController;

use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Controller\HealthController
 */
class HealthControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application | \PHPUnit_Framework_MockObject_MockObject
     */
    private $application;

    /**
     * @var Request | \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->request = $this->createMock(Request::class);
        $this->application = $this->createMock(Application::class);
    }

    /**
     * @covers ::healthzAction
     */
    public function testHealthzAction()
    {
        $controller = new HealthController();
        $this->assertEquals('ok', $controller->healthzAction($this->application, $this->request));
    }
}
