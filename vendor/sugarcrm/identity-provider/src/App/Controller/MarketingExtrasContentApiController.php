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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MarketingExtrasContentApiController
{

    /**
     * Gets and returns the marketing content URL
     *
     * @param Application $app
     * @param Request $request
     * @return JsonResponse
     */
    public function getMarketingContentUrlAction(Application $app, Request $request)
    {
        $url = $app->getMarketingExtrasService()->getUrl($request->get('tid', ''));
        return new JsonResponse($url);
    }
}
