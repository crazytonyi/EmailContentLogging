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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Twig;

use Sugarcrm\IdentityProvider\App\Application;

use Sugarcrm\IdentityProvider\App\Mango\MetadataService;
use Sugarcrm\IdentityProvider\App\Repository\TenantRepository;
use Sugarcrm\IdentityProvider\App\Twig\Extension;
use Sugarcrm\IdentityProvider\App\Twig\Functions\Tenant as TenantFunction;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Translation\Translator;

/**
 * Class ExtensionTest
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Twig\Extension
 */
class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    private const CONFIG = [
        'recaptcha' => [
            'sitekey' => 'sitekey',
        ],
        'honeypot' => [
            'name' => 'first_name',
        ],
        'locales' => [
            'en-US' => 'English (US)',
            'bg-BG' => 'Български',
            'cs-CZ' => 'Česky',
            'da-DK' => 'Dansk',
            'de-DE' => 'Deutsch',
            'el-EL' => 'Ελληνικά',
            'es-ES' => 'Español',
            'fr-FR' => 'Français',
        ],
    ];

    /**
     * @var Application|\PHPUnit_Framework_MockObject_MockObject
     */
    private $app;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var MetadataService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataService;

    /**
     * @var Translator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var UrlGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlGenerator;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->urlGenerator = $this->createMock(UrlGenerator::class);
        $this->translator = $this->createMock(Translator::class);

        $this->app = $this->createMock(Application::class);

        $this->session = $this->createMock(Session::class);
        $this->metadataService = $this->createMock(MetadataService::class);

        $this->app->expects($this->any())->method('getTranslator')->willReturn($this->translator);

        $this->app->method('getSession')->willReturn($this->session);
        $this->app->method('getTenantRepository')->willReturn(
            $this->createMock(TenantRepository::class)
        );
        $this->app->method('getConfig')->willReturn(self::CONFIG);
        $this->app->method('getUrlGeneratorService')->willReturn($this->urlGenerator);
    }

    /**
     * @covers ::getFunctions
     */
    public function testGetFunctions()
    {
        $extension = new Extension($this->app);

        $actual = false;
        foreach ($extension->getFunctions() as $function) {
            if (is_object($function) && $function instanceof TenantFunction) {
                $actual = true;
                break;
            }
        }

        $this->assertTrue($actual);
    }

    /**
     * @covers ::getGlobals
     */
    public function testGetGlobals()
    {
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('/marketingExtrasApi?url');

        $extension = new Extension($this->app);

        $globals = $extension->getGlobals();

        $this->assertArrayHasKey('recaptcha_sitekey', $globals);
        $this->assertArrayHasKey('marketingExtrasApi', $globals);
        $this->assertEquals('/marketingExtrasApi?url', $globals['marketingExtrasApi']);
    }

    /**
     * Provides data for testGetGlobalsLanguages
     * @return array
     */
    public function getGlobalsLanguagesProvider(): array
    {
        return [
            'noLanguagesInMetadata' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'metadataLanguages' => [],
                'expectedLanguages' => [
                    'en-US' => 'English (US)',
                    'bg-BG' => 'Български',
                    'cs-CZ' => 'Česky',
                    'da-DK' => 'Dansk',
                    'de-DE' => 'Deutsch',
                    'el-EL' => 'Ελληνικά',
                    'es-ES' => 'Español',
                    'fr-FR' => 'Français',
                ],
            ],
            'languagesInMetadata' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'metadataLanguages' => [
                    'bg-BG' => 'Български',
                    'da-DK' => 'Dansk',
                ],
                'expectedLanguages' => [
                    'bg-BG' => 'Български',
                    'da-DK' => 'Dansk',
                ],
            ],
            'noTenantInSession' => [
                'tenant' => null,
                'metadataLanguages' => [
                    'bg-BG' => 'Български',
                    'da-DK' => 'Dansk',
                ],
                'expectedLanguages' => [
                    'en-US' => 'English (US)',
                    'bg-BG' => 'Български',
                    'cs-CZ' => 'Česky',
                    'da-DK' => 'Dansk',
                    'de-DE' => 'Deutsch',
                    'el-EL' => 'Ελληνικά',
                    'es-ES' => 'Español',
                    'fr-FR' => 'Français',
                ],
            ],
            'notExistingLanguageInMetadata' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'metadataLanguages' => [
                    'bg-BG1' => 'Български',
                    'bg-BG' => 'Български',
                ],
                'expectedLanguages' => [
                    'bg-BG' => 'Български',
                ],
            ],
        ];
    }

    /**
     * @param string $tenant
     * @param array $metadataLanguages
     * @param array $expectedLanguages
     *
     * @covers ::getGlobals
     * @dataProvider getGlobalsLanguagesProvider
     */
    public function testGetGlobalsLanguages(?string $tenant, array $metadataLanguages, array $expectedLanguages): void
    {
        $extension = new Extension($this->app);
        $this->session->expects($this->once())->method('get')->with('tenant')->willReturn($tenant);
        $this->app->method('getMetadataService')->willReturn($this->metadataService);
        $this->metadataService->method('getLanguages')->with($tenant)->willReturn($metadataLanguages);

        $this->assertEquals($expectedLanguages, $extension->getGlobals()['locales']);
    }

    /**
     * @covers ::translateArray
     */
    public function testTranslateArrayString()
    {
        $extension = new Extension($this->app);
        $this->translator->expects($this->once())->method('trans')->with('string1')->willReturn('de.string1');
        $this->assertEquals('de.string1', $extension->translateArray('string1'));
    }

    /**
     * @covers ::translateArray
     */
    public function testTranslateArray()
    {
        $extension = new Extension($this->app);
        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->withConsecutive(['string1'], ['string2'])
            ->willReturnOnConsecutiveCalls('de.string1', 'de.string2');

        $this->assertEquals(['de.string1', 'de.string2'], $extension->translateArray(['string1', 'string2']));
    }
}
