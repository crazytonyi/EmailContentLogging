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

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class RestService
{
    public const REST_VERSION = '11_2';

    private $customEndpoints = [
        'login/marketingContentUrl' => '11_9',
    ];

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RestService constructor.
     * @param Client $httpClient
     * @param LoggerInterface $logger
     */
    public function __construct(Client $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * @param string $url
     * @param array $endpoint
     * @param array $params
     * @param string $etag
     * @return array
     */
    public function get(string $url, array $endpoint, array $params = [], string $etag = ''): array
    {
        $options = [];
        if ($etag) {
            $options['headers']['If-None-Match'] = $etag;
        }
        $requestUrl = $this->buildUrl($url, $endpoint, $params);
        try {
            $response = $this->httpClient->get($requestUrl, $options);
        } catch (\Exception $e) {
            $this->logger->warning(
                sprintf('REST request to %s fails with error: %s', $requestUrl, $e->getMessage())
            );
            return ['', ''];
        }
        if ($response->getStatusCode() === 200) {
            return [$response->getBody()->getContents(), $response->getHeader('ETag')[0] ?? ''];
        }

        if (!in_array($response->getStatusCode(), [200, 304])) {
            $this->logger->warning(
                sprintf('REST request to %s fails with error: %s', $requestUrl, $response->getStatusCode())
            );
        }

        return ['', ''];
    }

    /**
     * @param string $url
     * @param array $endpoint
     * @param array $params
     * @return string
     */
    private function buildUrl(string $url, array $endpoint, array $params = []): string
    {
        $endpointStr = implode('/', $endpoint);
        $restVersion = array_key_exists($endpointStr, $this->customEndpoints)
            ? $this->customEndpoints[$endpointStr] :static::REST_VERSION;

        $path = sprintf(
            '%s/rest/v%s/%s',
            rtrim($url, '/'),
            $restVersion,
            $endpointStr
        );

        if ($params) {
            $path .= '?' . http_build_query($params);
        }

        return $path;
    }
}
