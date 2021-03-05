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

namespace Sugarcrm\IdentityProvider\Tests\Functional;

use Silex\WebTestCase;
use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\MarketingExtras\MarketingExtrasService;
use Symfony\Component\HttpKernel\Client;

/**
 * Class PageFlowTest
 *
 * Test case to test basic Pages flow.
 *
 * @package Sugarcrm\IdentityProvider\Tests\Functional
 */
abstract class PageFlowTest extends WebTestCase
{
    /**
     * Client object for WebTestCase
     * @var Client
     */
    protected $webClient;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->webClient = $this->createClient();
        $this->webClient->followRedirects(false);
    }

    /**
     * @inheritdoc
     * @return mixed
     */
    public function createApplication()
    {
        require_once __DIR__ . '/../../vendor/autoload.php';
        $app = new Application(
            [
                'env' => Application::ENV_TESTS,
            ]
        );

        $marketingExtras = $this->createMock(MarketingExtrasService::class);
        $marketingExtras->method('getUrl')->willReturn('http://marketing.content');
        $marketingExtras->method('getStaticUrl')->willReturn(MarketingExtrasService::STATIC_PATH);
        $app['marketingExtras'] = $marketingExtras;

        return $app;
    }
}
