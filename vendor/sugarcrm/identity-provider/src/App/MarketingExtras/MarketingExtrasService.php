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
namespace Sugarcrm\IdentityProvider\App\MarketingExtras;

use Sugarcrm\IdentityProvider\Mango\LocaleMapping;
use Sugarcrm\IdentityProvider\Srn;

use Sugarcrm\IdentityProvider\App\Grpc\AppService;
use Sugarcrm\IdentityProvider\App\Mango;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Psr\Log\LoggerInterface;

class MarketingExtrasService
{
    public const STATIC_PATH = '/StaticMarketingContent/static.html';

    /**
     * Headers that block iframe rendering
     */
    private $blacklistedHeaders = [
        'x-frame-options', // todo need discuss https://www.sugarcrm.com/product-login-page-service with this header
        'frame-ancestors',// todo need discuss https://www.sugarcrm.com/product-login-page-service with this header
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $connectTimeoutMS;

    /**
     * @var int
     */
    private $timeoutMS;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $language;

    /**
     * @var AppService
     */
    private $grpcAppService;

    /**
     * @var Mango\RestService
     */
    private $restService;

    /**
     * MarketingExtrasService constructor.
     * @param array $config
     * @param string $language
     * @param AppService $grpcAppService
     * @param Mango\RestService $restService,
     * @param LoggerInterface $logger
     */
    public function __construct(
        array $config,
        string $language,
        AppService $grpcAppService,
        Mango\RestService $restService,
        LoggerInterface $logger
    ) {
        $this->baseUrl = $config['baseUrl'];
        $this->timeoutMS = intval($config['timeoutMS']);
        $this->connectTimeoutMS = intval($config['connectTimeoutMS']);

        $this->language = LocaleMapping::map($language);
        $this->grpcAppService = $grpcAppService;
        $this->restService = $restService;
        $this->logger = $logger;
    }

    /**
     * Return Static Url
     * @return string
     */
    public function getStaticUrl(): string
    {
        return self::STATIC_PATH;
    }

    /**
     * Gets Marketing content URL from Marketing endpoint
     *
     * @param string $tenant
     * @return string
     */
    public function getUrl(string $tenant = ''): string
    {
        if (empty($this->baseUrl)) {
            $this->logger->warning(
                'Empty marketing extras content url',
                ['tags' => ['IdM.rest.MarketingExtrasContentApi']]
            );
            return $this->getStaticUrl();
        }

        if ($this->isValidTenant($tenant)) {
            return $this->getUrlByTenant($tenant);
        }
        return $this->getUrlWithoutTenant();
    }

    /**
     * @return Client
     */
    protected function getHttpClient(): Client
    {
        return new Client([
            'curl' => [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => true,
                CURLOPT_CONNECTTIMEOUT_MS => $this->connectTimeoutMS,
                CURLOPT_TIMEOUT_MS => $this->timeoutMS,
            ]
        ]);
    }

    /**
     * Determines if the URL is reachable and if it can be displayed in an iframe
     *
     * @param string $url
     * @return bool
     */
    private function isContentDisplayable(string $url): bool
    {
        try {
            $res = $this->getHttpClient()->get($url);
        } catch (\Exception $e) {
            $this->logger->warning(
                'Could not get response from URL',
                [
                    'url' => $url,
                    'exception' => $e->getMessage(),
                    'tags' => ['IdM.rest.MarketingExtrasContentApi'],
                ]
            );
            return false;
        }

        foreach ($this->blacklistedHeaders as $blacklistedHeader) {
            if ($res->getHeader($blacklistedHeader)) {
                $this->logger->warning(
                    'Cannot load iframe due to header from URL',
                    [
                        'header' => $blacklistedHeader,
                        'url' => $url,
                        'tags' => ['IdM.rest.MarketingExtrasContentApi'],
                    ]
                );
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $tenant
     * @return bool
     */
    private function isValidTenant(string $tenant): bool
    {
        if (empty($tenant)) {
            return false;
        }
        try {
            Srn\Converter::fromString($tenant);
        } catch (\Exception $exception) {
            $this->logger->warning('Tenant id invalid', [
                'exception' => $exception->getMessage(),
                'tags' => ['IdM.rest.MarketingExtrasContentApi'],
            ]);
            return false;
        }
        return true;
    }

    /**
     * @param string $tenant
     * @return string
     */
    private function getUrlByTenant(string $tenant): string
    {
        $crmUrl = $this->grpcAppService->getCrmUrlByTenant($tenant);
        if (is_null($crmUrl)) {
            return $this->getUrlWithoutTenant();
        }

        //Retrieve url from Mango
        [$url] = $this->restService->get(
            $crmUrl,
            ['login', 'marketingContentUrl'],
            ['selected_language' => $this->language]
        );
        $url = $this->jsonDecode($url);

        if ($url && Uri::isAbsolute(new Uri($url))) {
            return $url;
        }

        $crmUrlArr = parse_url($crmUrl);
        $queryParams = [
            'language' => $this->language,
            'domain' => $crmUrlArr['host'] . (isset($crmUrlArr['port']) ? ':' . $crmUrlArr['port'] : ''),
        ];
        $url = $this->buildFullUrl($queryParams);
        if ($this->isContentDisplayable($url)) {
            return $url;
        }
        return $this->getStaticUrl();
    }

    /**
     * @return string
     */
    private function getUrlWithoutTenant(): string
    {
        $queryParams = [
            'language' => $this->language,
        ];
        $url = $this->buildFullUrl($queryParams);
        if ($this->isContentDisplayable($url)) {
            return $url;
        }
        return $this->getStaticUrl();
    }

    /**
     * @param array $queryParams
     * @return string
     */
    private function buildFullUrl(array $queryParams): string
    {
        return $this->baseUrl . '?' . http_build_query($queryParams);
    }

    /**
     * @param string $text
     * @return string
     */
    private function jsonDecode(string $text): string
    {
        try {
            return \GuzzleHttp\json_decode($text, true);
        } catch (\Exception $e) {
            return '';
        }
    }
}
