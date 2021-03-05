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

use Sugarcrm\IdentityProvider\App\MarketingExtras\MarketingExtrasService;

/**
 * ChangePassword page functional tests
 */
class ChangePasswordPageTest extends PageFlowTest
{

    /**
     * Tests Marketing Extras iframe in change password page
     */
    public function testMarketingExtrasContentInChangePasswordPage(): void
    {
        $this->webClient->request('GET', 'password/forgot?tenant_hint=2000000001');
        $response = $this->webClient->getResponse();
        $body = $response->getContent();

        $this->assertContains('<iframe', $body);
        $this->assertContains(MarketingExtrasService::STATIC_PATH, $body);
        $this->assertContains('welcome-with-marketing', $body);
        $this->assertContains('alert-top-with-marketing', $body);
    }
}
