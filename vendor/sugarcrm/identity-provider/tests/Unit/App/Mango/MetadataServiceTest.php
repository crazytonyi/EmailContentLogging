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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Mango;

use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use Sugarcrm\IdentityProvider\App\Mango\MetadataService;
use Sugarcrm\IdentityProvider\App\Mango\RestService;
use Sugarcrm\IdentityProvider\App\Repository\MetadataRepository;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Mango\MetadataService
 */
class MetadataServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RestService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $httpService;

    /**
     * @var MetadataRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var MetadataService
     */
    private $service;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->httpService = $this->createMock(RestService::class);
        $this->repository = $this->createMock(MetadataRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new MetadataService($this->httpService, $this->repository, $this->logger);
    }

    /**
     * Provides data for testGetLanguagesFromSourceWithSaveInDatabase
     * @return array
     */
    public function getLanguagesFromSourceWithSaveInDatabaseProvider(): array
    {
        return [
            'noDataInDatabaseShortTenant' => [
                'tenant' => '1396472465',
                'url' => 'http://mango.url',
                'dbData' => [],
                'restData' => [
                    json_encode([1, 2, 3]),
                    '123'
                ],
                'expectedUrl' => 'http://mango.url',
                'expectedEtag' => '',
                'expectedTenant' => '1396472465',
                'expectedResult' => [1, 2, 3],
            ],
            'noDataInDatabaseSrnTenant' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'url' => 'http://mango.url',
                'dbData' => [],
                'restData' => [
                    json_encode([1, 2, 3]),
                    '123'
                ],
                'expectedUrl' => 'http://mango.url',
                'expectedEtag' => '',
                'expectedTenant' => '1396472465',
                'expectedResult' => [1, 2, 3],
            ],
            'metadataInDatabaseEtagChanged' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'url' => 'http://mango.url',
                'dbData' => [
                    'tenant_id' => '1396472465',
                    'metadata' => json_encode([1, 2, 3]),
                    'metadata_hash' => '345',
                    'metadata_source' => 'http://mango.url',
                ],
                'restData' => [
                    json_encode([1, 2, 3]),
                    '123'
                ],
                'expectedUrl' => 'http://mango.url',
                'expectedEtag' => '345',
                'expectedTenant' => '1396472465',
                'expectedResult' => [1, 2, 3],
            ],
            'metadataInDatabaseNoUrl' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'url' => '',
                'dbData' => [
                    'tenant_id' => '1396472465',
                    'metadata' => json_encode([1, 2, 3]),
                    'metadata_hash' => '345',
                    'metadata_source' => 'http://mango1.url',
                ],
                'restData' => [
                    json_encode([1, 2, 3]),
                    '123'
                ],
                'expectedUrl' => 'http://mango1.url',
                'expectedEtag' => '345',
                'expectedTenant' => '1396472465',
                'expectedResult' => [1, 2, 3],
            ],
        ];
    }

    /**
     * @param string $tenant
     * @param string $url
     * @param array $dbData
     * @param array $restData
     * @param string $expectedUrl
     * @param string $expectedEtag
     * @param string $expectedTenant
     * @param array $expectedResult
     *
     * @dataProvider getLanguagesFromSourceWithSaveInDatabaseProvider
     * @covers ::getLanguagesFromSource
     */
    public function testGetLanguagesFromSourceWithSaveInDatabase(
        string $tenant,
        string $url,
        array $dbData,
        array $restData,
        string $expectedUrl,
        string $expectedEtag,
        string $expectedTenant,
        array $expectedResult
    ): void {
        $this->repository->expects($this->once())
            ->method('getByTenant')
            ->with($expectedTenant, MetadataService::METADATA_LANGUAGES_PARAM)
            ->willReturn($dbData);

        $this->httpService->expects($this->once())
            ->method('get')
            ->with(
                $expectedUrl,
                ['metadata', 'public'],
                ['type_filter' => MetadataService::METADATA_LANGUAGES_PARAM],
                $expectedEtag
            )->willReturn($restData);

        $this->repository->expects($this->once())
            ->method('store')
            ->with(
                $expectedTenant,
                MetadataService::METADATA_LANGUAGES_PARAM,
                $restData[0],
                $restData[1],
                $expectedUrl
            );

        $metadata = $this->service->getLanguagesFromSource($tenant, $url);
        $this->assertEquals($expectedResult, $metadata);
    }

    /**
     * @covers ::getLanguagesFromSource
     */
    public function testGetLanguagesFromSourceMetadataNotChangedOnSource(): void
    {
        $tenant = 'srn:dev:iam:na:1396472465:tenant';
        $url = 'http://mango.url';
        $dbData = [
            'tenant_id' => '1396472465',
            'metadata' => json_encode([1, 2, 3]),
            'metadata_hash' => '345',
            'metadata_source' => 'http://mango.url',
        ];
        $restData = ['', ''];
        $this->repository->expects($this->once())
            ->method('getByTenant')
            ->with('1396472465', MetadataService::METADATA_LANGUAGES_PARAM)
            ->willReturn($dbData);

        $this->httpService->expects($this->once())
            ->method('get')
            ->with(
                $url,
                ['metadata', 'public'],
                ['type_filter' => MetadataService::METADATA_LANGUAGES_PARAM],
                '345'
            )->willReturn($restData);

        $this->repository->expects($this->never())->method('store');

        $metadata = $this->service->getLanguagesFromSource($tenant, $url);
        $this->assertEquals([1, 2, 3], $metadata);
    }

    /**
     * @covers ::getLanguagesFromSource
     */
    public function testGetLanguagesFromSourceWithoutUrl(): void
    {
        $tenant = 'srn:dev:iam:na:1396472465:tenant';
        $url = '';
        $dbData = [
            'tenant_id' => '1396472465',
            'metadata' => json_encode([1, 2, 3]),
            'metadata_hash' => '345',
            'metadata_source' => '',
        ];
        $this->repository->expects($this->once())
            ->method('getByTenant')
            ->with('1396472465', MetadataService::METADATA_LANGUAGES_PARAM)
            ->willReturn($dbData);

        $this->repository->expects($this->never())->method('store');

        $metadata = $this->service->getLanguagesFromSource($tenant, $url);
        $this->assertEquals([1, 2, 3], $metadata);
    }

    public function testGetLanguagesFromSourceDBInsertError(): void
    {
        $tenant = 'srn:dev:iam:na:1396472465:tenant';
        $url = '';
        $dbData = [
            'tenant_id' => '1396472465',
            'metadata' => json_encode([1, 2, 3]),
            'metadata_hash' => '345',
            'metadata_source' => 'http://mango.url',
        ];
        $restData = ['text', 'hash'];

        $this->repository->expects($this->once())
            ->method('getByTenant')
            ->with('1396472465', MetadataService::METADATA_LANGUAGES_PARAM)
            ->willReturn($dbData);

        $this->httpService->expects($this->once())
            ->method('get')
            ->with(
                'http://mango.url',
                ['metadata', 'public'],
                ['type_filter' => MetadataService::METADATA_LANGUAGES_PARAM],
                '345'
            )->willReturn($restData);

        $this->repository->expects($this->once())
            ->method('store')
            ->with(
                '1396472465',
                MetadataService::METADATA_LANGUAGES_PARAM,
                'text',
                'hash',
                'http://mango.url'
            )->willThrowException(new DBALException());

        $this->logger->expects($this->once())->method('warning');

        $metadata = $this->service->getLanguagesFromSource($tenant, $url);
        $this->assertEquals([1, 2, 3], $metadata);
    }

    /**
     * Provides data for testGetLanguages
     * @return array
     */
    public function getLanguagesProvider(): array
    {
        return [
            'emptyMetadata' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'dbData' => [],
                'expectedTenant' => '1396472465',
                'expectedResult' => [],
            ],
            'noLabelsSectionInMetadata' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'dbData' => [
                    'tenant_id' => '1396472465',
                    'metadata' => json_encode([
                        'modules' => ['login' => []]
                    ]),
                    'metadata_hash' => '345',
                    'metadata_source' => '',
                ],
                'expectedTenant' => '1396472465',
                'expectedResult' => [],
            ],
            'labelsSectionInMetadata' => [
                'tenant' => '1396472465',
                'dbData' => [
                    'tenant_id' => '1396472465',
                    'metadata' => json_encode([
                        'modules' => ['login' => []],
                        'labels' => [
                            'en_us' => 'cache/api/metadata/lang_en_us_base_public_ordered.json',
                            'de_DE' => 'cache/api/metadata/lang_de_DE_base_public_ordered.json',
                            'el_EL' => 'cache/api/metadata/lang_el_EL_base_public_ordered.json',
                            'he_IL' => 'cache/api/metadata/lang_he_IL_base_public_ordered.json',
                            'hu_HU' => 'cache/api/metadata/lang_hu_HU_base_public_ordered.json',
                            'hr_HR' => 'cache/api/metadata/lang_hr_HR_base_public_ordered.json',
                            'it_it' => 'cache/api/metadata/lang_it_it_base_public_ordered.json',
                            'lt_LT' => 'cache/api/metadata/lang_lt_LT_base_public_ordered.json',
                            'ja_JP' => 'cache/api/metadata/lang_ja_JP_base_public_ordered.json',
                            'ko_KR' => 'cache/api/metadata/lang_ko_KR_base_public_ordered.json',
                            'lv_LV' => 'cache/api/metadata/lang_lv_LV_base_public_ordered.json',
                            'nb_NO' => 'cache/api/metadata/lang_nb_NO_base_public_ordered.json',
                            'nl_NL' => 'cache/api/metadata/lang_nl_NL_base_public_ordered.json',
                            'pl_PL' => 'cache/api/metadata/lang_pl_PL_base_public_ordered.json',
                            'pt_PT' => 'cache/api/metadata/lang_pt_PT_base_public_ordered.json',
                            'ro_RO' => 'cache/api/metadata/lang_ro_RO_base_public_ordered.json',
                            'ru_RU' => 'cache/api/metadata/lang_ru_RU_base_public_ordered.json',
                            'sv_SE' => 'cache/api/metadata/lang_sv_SE_base_public_ordered.json',
                            'th_TH' => 'cache/api/metadata/lang_th_TH_base_public_ordered.json',
                            'tr_TR' => 'cache/api/metadata/lang_tr_TR_base_public_ordered.json',
                            'zh_TW' => 'cache/api/metadata/lang_zh_TW_base_public_ordered.json',
                            'zh_CN' => 'cache/api/metadata/lang_zh_CN_base_public_ordered.json',
                            'pt_BR' => 'cache/api/metadata/lang_pt_BR_base_public_ordered.json',
                            'ca_ES' => 'cache/api/metadata/lang_ca_ES_base_public_ordered.json',
                            'sr_RS' => 'cache/api/metadata/lang_sr_RS_base_public_ordered.json',
                            'sk_SK' => 'cache/api/metadata/lang_sk_SK_base_public_ordered.json',
                            'sq_AL' => 'cache/api/metadata/lang_sq_AL_base_public_ordered.json',
                            'et_EE' => 'cache/api/metadata/lang_et_EE_base_public_ordered.json',
                            'es_LA' => 'cache/api/metadata/lang_es_LA_base_public_ordered.json',
                            'fi_FI' => 'cache/api/metadata/lang_fi_FI_base_public_ordered.json',
                            'ar_SA' => 'cache/api/metadata/lang_ar_SA_base_public_ordered.json',
                            'uk_UA' => 'cache/api/metadata/lang_uk_UA_base_public_ordered.json',
                            'default' => 'de_DE',
                        ],
                    ]),
                    'metadata_hash' => '345',
                    'metadata_source' => '',
                ],
                'expectedTenant' => '1396472465',
                'expectedResult' => [
                    'en-US' => true,
                    'de-DE' => true,
                    'el-EL' => true,
                    'he-IL' => true,
                    'hu-HU' => true,
                    'hr-HR' => true,
                    'it-iT' => true,
                    'lt-LT' => true,
                    'ja-JP' => true,
                    'ko-KR' => true,
                    'lv-LV' => true,
                    'nb-NO' => true,
                    'nl-NL' => true,
                    'pl-PL' => true,
                    'pt-PT' => true,
                    'ro-RO' => true,
                    'ru-RU' => true,
                    'sv-SE' => true,
                    'th-TH' => true,
                    'tr-TR' => true,
                    'zh-TW' => true,
                    'zh-CN' => true,
                    'pt-BR' => true,
                    'ca-ES' => true,
                    'sr-RS' => true,
                    'sk-SK' => true,
                    'sq-AL' => true,
                    'et-EE' => true,
                    'es-LA' => true,
                    'fi-FI' => true,
                    'ar-SA' => true,
                    'uk-UA' => true,
                ],
            ],
        ];
    }

    /**
     * @param string $tenant
     * @param array $dbData
     * @param string $expectedTenant
     * @param array $expectedResult
     *
     * @covers ::getLanguages
     * @dataProvider getLanguagesProvider
     */
    public function testGetLanguages(string $tenant, array $dbData, string $expectedTenant, array $expectedResult): void
    {
        $this->repository->expects($this->once())
            ->method('getByTenant')
            ->with($expectedTenant)
            ->willReturn($dbData);

        $this->assertEquals($expectedResult, $this->service->getLanguages($tenant));
    }

    /**
     * Provides data for testGetLanguages
     * @return array
     */
    public function getDefaultLanguageProvider(): array
    {
        return [
            'emptyMetadata' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'dbData' => [],
                'expectedTenant' => '1396472465',
                'expectedResult' => null,
            ],
            'noLabelsSectionInMetadata' => [
                'tenant' => 'srn:dev:iam:na:1396472465:tenant',
                'dbData' => [
                    'tenant_id' => '1396472465',
                    'metadata' => json_encode([
                        'modules' => ['login' => []]
                    ]),
                    'metadata_hash' => '345',
                    'metadata_source' => '',
                ],
                'expectedTenant' => '1396472465',
                'expectedResult' => null,
            ],
            'labelsSectionInMetadataNoDefaultLanguage' => [
                'tenant' => '1396472465',
                'dbData' => [
                    'tenant_id' => '1396472465',
                    'metadata' => json_encode([
                        'modules' => ['login' => []],
                        'labels' => [
                            'en_us' => 'cache/api/metadata/lang_en_us_base_public_ordered.json',
                            'de_DE' => 'cache/api/metadata/lang_de_DE_base_public_ordered.json',
                            'el_EL' => 'cache/api/metadata/lang_el_EL_base_public_ordered.json',
                            'he_IL' => 'cache/api/metadata/lang_he_IL_base_public_ordered.json',
                            'hu_HU' => 'cache/api/metadata/lang_hu_HU_base_public_ordered.json',
                        ],
                    ]),
                    'metadata_hash' => '345',
                    'metadata_source' => '',
                ],
                'expectedTenant' => '1396472465',
                'expectedResult' => null,
            ],
            'labelsSectionInMetadataHasDefaultLanguage' => [
                'tenant' => '1396472465',
                'dbData' => [
                    'tenant_id' => '1396472465',
                    'metadata' => json_encode([
                        'modules' => ['login' => []],
                        'labels' => [
                            'de_DE' => 'cache/api/metadata/lang_de_DE_base_public_ordered.json',
                            'el_EL' => 'cache/api/metadata/lang_el_EL_base_public_ordered.json',
                            'he_IL' => 'cache/api/metadata/lang_he_IL_base_public_ordered.json',
                            'hu_HU' => 'cache/api/metadata/lang_hu_HU_base_public_ordered.json',
                            'default' => 'el_EL'
                        ],
                    ]),
                    'metadata_hash' => '345',
                    'metadata_source' => '',
                ],
                'expectedTenant' => '1396472465',
                'expectedResult' => 'el-EL',
            ],
            'labelsSectionInMetadataDefaultLanguageNotInList' => [
                'tenant' => '1396472465',
                'dbData' => [
                    'tenant_id' => '1396472465',
                    'metadata' => json_encode([
                        'modules' => ['login' => []],
                        'labels' => [
                            'de_DE' => 'cache/api/metadata/lang_de_DE_base_public_ordered.json',
                            'el_EL' => 'cache/api/metadata/lang_el_EL_base_public_ordered.json',
                            'he_IL' => 'cache/api/metadata/lang_he_IL_base_public_ordered.json',
                            'hu_HU' => 'cache/api/metadata/lang_hu_HU_base_public_ordered.json',
                            'default' => 'en_us'
                        ],
                    ]),
                    'metadata_hash' => '345',
                    'metadata_source' => '',
                ],
                'expectedTenant' => '1396472465',
                'expectedResult' => 'de-DE',
            ],
        ];
    }

    /**
     * @param string $tenant
     * @param array $dbData
     * @param string $expectedTenant
     * @param string $expectedResult
     *
     * @covers ::getDefaultLanguage
     * @dataProvider getDefaultLanguageProvider
     */
    public function testGetDefaultLanguage(
        string $tenant,
        array $dbData,
        string $expectedTenant,
        ?string $expectedResult
    ): void {
        $this->repository->expects($this->once())
            ->method('getByTenant')
            ->with($expectedTenant)
            ->willReturn($dbData);

        $this->assertEquals($expectedResult, $this->service->getDefaultLanguage($tenant));
    }
}
