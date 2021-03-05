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

use Sugarcrm\IdentityProvider\App\Grpc\AppService;
use Sugarcrm\IdentityProvider\App\Application;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AppServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['grpcAppService'] = function ($app) {
            /** @var $app Application */
            return new AppService($app['config']['grpc']['disabled'], $app->getGrpcAppApi(), $app->getLogger());
        };
    }
}
