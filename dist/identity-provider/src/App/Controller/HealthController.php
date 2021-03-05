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

namespace Sugarcrm\IdentityProvider\App\Controller;

use Sugarcrm\IdentityProvider\App\Application;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class HealthController.
 */
class HealthController
{
    /**
     * @param Application $app Silex application instance.
     * @param Request $request
     * @return string
     */
    public function healthzAction(Application $app, Request $request)
    {
        return 'ok';
    }
}
