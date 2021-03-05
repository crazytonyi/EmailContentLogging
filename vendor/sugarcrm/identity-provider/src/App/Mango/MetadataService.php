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

namespace Sugarcrm\IdentityProvider\App\Mango;

use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use Sugarcrm\IdentityProvider\App\Repository\MetadataRepository;
use Sugarcrm\IdentityProvider\Srn\Converter;

class MetadataService
{
    /**
     * @var RestService
     */
    private $httpService;

    /**
     * @var MetadataRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    protected static $specialLanguages = [
        'it-IT' => 'it-iT',
    ];

    public const METADATA_LANGUAGES_PARAM = 'labels';

    /**
     * MetadataService constructor.
     * @param RestService $service
     * @param MetadataRepository $repository
     * @param LoggerInterface $logger
     */
    public function __construct(RestService $service, MetadataRepository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->httpService = $service;
        $this->logger = $logger;
    }

    /**
     * Get metadata from URL
     * @param string $tenant
     * @param string $url
     * @return array
     */
    public function getLanguagesFromSource(string $tenant, string $url): array
    {
        $tenantId = $this->getTenantId($tenant);
        $metadata = $this->repository->getByTenant($tenantId, static::METADATA_LANGUAGES_PARAM);

        if (!empty($url)) {
            $sourceUrl = $url;
        } elseif (!empty($metadata['metadata_source'])) {
            $sourceUrl = $metadata['metadata_source'];
        }

        if (!empty($sourceUrl)) {
            $etag = $metadata['metadata_hash'] ?? '';
            [$metadataText, $metadataHash] = $this->httpService->get(
                $sourceUrl,
                ['metadata', 'public'],
                ['type_filter' => static::METADATA_LANGUAGES_PARAM],
                $etag
            );
            if ($metadataText) {
                try {
                    $this->repository->store(
                        $tenantId,
                        static::METADATA_LANGUAGES_PARAM,
                        $metadataText,
                        $metadataHash,
                        $sourceUrl
                    );
                    return $this->jsonDecode($metadataText);
                } catch (DBALException $e) {
                    $this->logger->warning(
                        sprintf(
                            'Language metadata save fails with error: %s for %s (%s)',
                            $e->getMessage(),
                            $tenantId,
                            $sourceUrl
                        )
                    );
                }
            }
        }

        if (!empty($metadata['metadata'])) {
            return $this->jsonDecode($metadata['metadata']);
        }

        return [];
    }

    /**
     * @param string $tenant
     * @return array
     */
    public function getLanguages(string $tenant): array
    {
        $tenantId = $this->getTenantId($tenant);
        $dbMetadata = $this->repository->getByTenant($tenantId, static::METADATA_LANGUAGES_PARAM);
        if (empty($dbMetadata['metadata'])) {
            return [];
        }

        $metadata = $this->jsonDecode($dbMetadata['metadata']);

        if (empty($metadata[static::METADATA_LANGUAGES_PARAM]) ||
            !is_array($metadata[static::METADATA_LANGUAGES_PARAM])) {
            return [];
        }

        $result = [];
        foreach ($metadata[static::METADATA_LANGUAGES_PARAM] as $lang => $path) {
            $appLanguage = $this->metadataLanguageToApplicationLanguage($lang);
            if (!$appLanguage) {
                continue;
            }

            $result[$appLanguage] = true;
        }

        return $result;
    }

    /**
     * @param string $tenant
     * @return string|null
     */
    public function getDefaultLanguage(string $tenant): ?string
    {
        $tenantId = $this->getTenantId($tenant);
        $dbMetadata = $this->repository->getByTenant($tenantId, static::METADATA_LANGUAGES_PARAM);
        if (empty($dbMetadata['metadata'])) {
            return null;
        }

        $metadata = $this->jsonDecode($dbMetadata['metadata']);

        if (empty($metadata[static::METADATA_LANGUAGES_PARAM])) {
            return null;
        }

        $languagesList = $metadata[static::METADATA_LANGUAGES_PARAM];

        if (isset($languagesList['default'])) {
            $defaultLanguage = $languagesList['default'];
            if (!isset($languagesList[$defaultLanguage])) {
                reset($languagesList);
                $firstLanguage = key($languagesList);
                return $this->metadataLanguageToApplicationLanguage($firstLanguage);
            }

            return $this->metadataLanguageToApplicationLanguage($languagesList['default']);
        }

        return null;
    }

    /**
     * @param string $lang
     * @return string|null
     */
    private function metadataLanguageToApplicationLanguage(string $lang): ?string
    {
        $languageParts = explode('_', $lang);

        if (count($languageParts) === 1) {
            return null;
        }

        $languageParts[1] = strtoupper($languageParts[1]);
        $result = implode('-', $languageParts);
        return static::$specialLanguages[$result] ?? $result;
    }

    /**
     * @param string $tenant
     * @return string
     */
    private function getTenantId(string $tenant): string
    {
        try {
            $srn = Converter::fromString($tenant);
            return $srn->getTenantId();
        } catch (\InvalidArgumentException $e) {
            return $tenant;
        }
    }

    /**
     * @param string $text
     * @return array
     */
    private function jsonDecode(string $text): array
    {
        try {
            return \GuzzleHttp\json_decode($text, true);
        } catch (\Exception $e) {
            return [];
        }
    }
}
