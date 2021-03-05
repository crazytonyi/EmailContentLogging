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

namespace Sugarcrm\IdentityProvider\App\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

use Sugarcrm\IdentityProvider\App\Instrumentation\NewRelicProfiler;

/**
 * Class NewRelicProfilerProvider
 * @package Sugarcrm\IdentityProvider\App\Provider
 */
class NewRelicProfilerProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     */
    public function register(Container $app): void
    {
        $app['newRelicProfiler'] = function ($app) {
            return new NewRelicProfiler($app);
        };
    }
}
