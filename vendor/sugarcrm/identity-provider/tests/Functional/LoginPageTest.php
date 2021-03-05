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
 * Login page functional tests
 */
class LoginPageTest extends PageFlowTest
{
    /**
     * Tests Marketing Extras iframe in choose tenant page
     */
    public function testMarketingExtrasContentInChooseTenantPage(): void
    {
        $this->webClient->request('GET', '/');
        $response = $this->webClient->getResponse();
        $body = $response->getContent();
        $this->assertContains('<iframe', $body);
        $this->assertContains(MarketingExtrasService::STATIC_PATH, $body);
        $this->assertContains('welcome-with-marketing', $body);
        $this->assertContains('alert-top-with-marketing', $body);
    }

    /**
     * Tests Marketing Extras iframe in login page
     */
    public function testMarketingExtrasContentInLoginPage(): void
    {
        $this->webClient->request('GET', '/?tenant_hint=2000000001');
        $response = $this->webClient->getResponse();
        $body = $response->getContent();

        $this->assertContains('<iframe', $body);
        $this->assertContains(MarketingExtrasService::STATIC_PATH, $body);
        $this->assertContains('welcome-with-marketing', $body);
        $this->assertContains('alert-top-with-marketing', $body);
    }

    /**
     * @return array
     * @see testRtlLocales
     */
    public function getRtlLocales(): array
    {
        return [
            ['he-IL'],
            ['ar-SA'],
        ];
    }

    /**
     * Tests Rtl Locales
     * @dataProvider getRtlLocales
     * @param string $locale
     */
    public function testRtlLocales(string $locale): void
    {
        $this->webClient->request('GET', '/?ulcid=' . $locale);
        $response = $this->webClient->getResponse();
        $body = $response->getContent();
        $this->assertContains('<html class="rtl"', $body);
    }

    /**
     * @return array
     * @see testRtlLocales
     */
    public function getLtrLocales(): array
    {
        return [
            [''],
            ['en-US'],
            ['de-DE'],
        ];
    }

    /**
     * Tests ltr Locales
     * @dataProvider getLtrLocales
     * @param string $locale
     */
    public function testLtrLocales(string $locale): void
    {
        $this->webClient->request('GET', '/?ulcid=' . $locale);
        $response = $this->webClient->getResponse();
        $body = $response->getContent();
        $this->assertNotContains('class="rtl"', $body);
    }
}
