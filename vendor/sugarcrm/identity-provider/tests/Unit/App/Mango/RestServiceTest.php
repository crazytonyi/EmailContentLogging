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

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Sugarcrm\IdentityProvider\App\Mango\MetadataService;
use Sugarcrm\IdentityProvider\App\Mango\RestService;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Mango\RestService
 */
class RestServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client | \PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClient;

    /**
     * @var RestService
     */
    private $restService;

    /**
     * @var ResponseInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var LoggerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var StreamInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseBody;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->httpClient = $this->createMock(Client::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->responseBody = $this->createMock(StreamInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->restService = new RestService($this->httpClient, $this->logger);
    }

    public function getProvider(): array
    {
        return [
            'etagInHeader200response' => [
                'url' => 'http://test',
                'endpoint' => ['metadata', 'p'],
                'params' => ['type_filter' => MetadataService::METADATA_LANGUAGES_PARAM],
                'etag' => 'tag',
                'responseCode' => 200,
                'responseBody' => 'body',
                'expectedUrl' => 'http://test/rest/v11_2/metadata/p?type_filter=labels',
                'expectedOptions' => [
                    'headers' => [
                        'If-None-Match' => 'tag',
                    ],
                ],
                'expectedBody' => 'body',
                'expectedEtag' => 'tag',
            ],
            'VersionForCustomEndpoints' => [
                'url' => 'http://test',
                'endpoint' => ['login', 'marketingContentUrl'],
                'params' => [],
                'etag' => 'tag',
                'responseCode' => 200,
                'responseBody' => 'body',
                'expectedUrl' => 'http://test/rest/v11_9/login/marketingContentUrl',
                'expectedOptions' => [
                    'headers' => [
                        'If-None-Match' => 'tag',
                    ],
                ],
                'expectedBody' => 'body',
                'expectedEtag' => 'tag',
            ],
            'etagInHeader304response' => [
                'url' => 'http://test',
                'endpoint' => ['metadata', 'p'],
                'params' => [],
                'etag' => 'tag',
                'responseCode' => 304,
                'responseBody' => 'body',
                'expectedUrl' => 'http://test/rest/v11_2/metadata/p',
                'expectedOptions' => [
                    'headers' => [
                        'If-None-Match' => 'tag',
                    ],
                ],
                'expectedBody' => '',
                'expectedEtag' => '',
            ],
            'NoEtagInHeader200response' => [
                'url' => 'http://test',
                'endpoint' => ['metadata', 'p'],
                'params' => [],
                'etag' => '',
                'responseCode' => 200,
                'responseBody' => 'body',
                'expectedUrl' => 'http://test/rest/v11_2/metadata/p',
                'expectedOptions' => [],
                'expectedBody' => 'body',
                'expectedEtag' => 'tag1',
            ],
        ];
    }

    /**
     * @param string $url
     * @param array $endpoint
     * @param array $params
     * @param string $etag
     * @param int $responseCode
     * @param string $responseBody
     * @param string $expectedUrl
     * @param array $expectedOptions
     * @param string $expectedBody
     * @param string $expectedEtag
     *
     * @covers ::get
     * @dataProvider getProvider
     */
    public function testGet(
        string $url,
        array $endpoint,
        array $params,
        string $etag,
        int $responseCode,
        string $responseBody,
        string $expectedUrl,
        array $expectedOptions,
        string $expectedBody,
        string $expectedEtag
    ): void {
        $this->httpClient->expects($this->once())
            ->method('__call')
            ->with('get', [$expectedUrl, $expectedOptions])
            ->willReturn($this->response);
        $this->response->method('getStatusCode')->willReturn($responseCode);
        $this->response->method('getBody')->willReturn($this->responseBody);
        $this->response->method('getHeader')->with('ETag')->willReturn([$expectedEtag]);
        $this->responseBody->method('getContents')->willReturn($responseBody);
        [$body, $etag] = $this->restService->get($url, $endpoint, $params, $etag);

        $this->assertEquals($expectedBody, $body);
        $this->assertEquals($expectedEtag, $etag);
    }

    /**
     * @covers ::get
     */
    public function testGetWithHttpException(): void
    {
        $url = 'http://test';
        $endpoint = ['metadata', 'p'];
        $etag = 'tag';
        $this->httpClient->expects($this->once())
            ->method('__call')
            ->willThrowException(new \InvalidArgumentException());
        $this->logger->expects($this->once())->method('warning');
        [$body, $etag] = $this->restService->get($url, $endpoint, [], $etag);

        $this->assertEquals('', $body);
        $this->assertEquals('', $etag);
    }

    /**
     * @covers ::get
     */
    public function testGetWithUnexpectedCode(): void
    {
        $url = 'http://test';
        $endpoint = ['metadata', 'p'];
        $etag = '';
        $this->httpClient->expects($this->once())
            ->method('__call')
            ->with('get', ['http://test/rest/v11_2/metadata/p', []])
            ->willReturn($this->response);
        $this->response->method('getStatusCode')->willReturn(404);
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('REST request to http://test/rest/v11_2/metadata/p fails with error: 404');
        [$body, $etag] = $this->restService->get($url, $endpoint, [], $etag);
        $this->assertEquals('', $body);
        $this->assertEquals('', $etag);
    }
}
