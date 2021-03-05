<?php

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Repository;

use Doctrine\DBAL\Connection;
use Sugarcrm\IdentityProvider\App\Repository\MetadataRepository;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Repository\MetadataRepository
 */
class MetadataRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection | \PHPUnit_Framework_MockObject_MockObject
     */
    private $db;

    /**
     * @var MetadataRepository
     */
    private $metadataRepository;

    protected function setUp()
    {
        $this->db = $this->createMock(Connection::class);
        $this->metadataRepository = new MetadataRepository($this->db);
    }

    /**
     * @covers ::getByTenant
     */
    public function testGetByTenantWithStaticCache(): void
    {
        $dbData = [
            'tenant_id' => '1396472465',
            'metadata' => json_encode([1, 2, 3]),
            'metadata_hash' => '345',
            'metadata_source' => 'http://mango.url',
        ];
        $dbData1 = [
            'tenant_id' => '1396472466',
            'metadata' => json_encode([1, 2, 3]),
            'metadata_hash' => '345',
            'metadata_source' => 'http://mango.url',
        ];

        $this->db->expects($this->exactly(2))
            ->method('fetchAssoc')
            ->withConsecutive(
                [$this->anything(), $this->equalTo(['1396472465', 'labels'])],
                [$this->anything(), $this->equalTo(['1396472466', 'labels1'])]
            )->willReturnOnConsecutiveCalls($dbData, $dbData1);

        $this->assertEquals($dbData, $this->metadataRepository->getByTenant('1396472465', 'labels'));
        $this->assertEquals($dbData, $this->metadataRepository->getByTenant('1396472465', 'labels'));

        $this->assertEquals($dbData1, $this->metadataRepository->getByTenant('1396472466', 'labels1'));
        $this->assertEquals($dbData1, $this->metadataRepository->getByTenant('1396472466', 'labels1'));
    }
}
