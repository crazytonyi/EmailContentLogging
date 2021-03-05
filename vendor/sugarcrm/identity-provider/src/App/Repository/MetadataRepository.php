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

namespace Sugarcrm\IdentityProvider\App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\ParameterType;

class MetadataRepository
{
    public const TABLE = 'tenant_metadata';

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var array
     */
    private static $metadata;

    /**
     * Consent repository constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $tenantId
     * @param string $metadataSection
     * @return array
     */
    public function getByTenant(string $tenantId, string $metadataSection): array
    {
        if (!empty(static::$metadata[$tenantId][$metadataSection])) {
            return static::$metadata[$tenantId][$metadataSection];
        }
        try {
            $data = $this->db->fetchAssoc(
                sprintf('SELECT * FROM %s WHERE tenant_id = ? and metadata_section = ?', self::TABLE),
                [$tenantId, $metadataSection]
            );
        } catch (DBALException $e) {
            return [];
        }

        if ($data) {
            static::$metadata[$tenantId][$metadataSection] = $data;
            return static::$metadata[$tenantId][$metadataSection];
        }

        return [];
    }

    /**
     * @param string $tenantId
     * @param string $metadataSection
     * @param string $metadataText
     * @param string $metadataHash
     * @param string $metadataSource
     * @throws DBALException
     */
    public function store(
        string $tenantId,
        string $metadataSection,
        string $metadataText,
        string $metadataHash,
        string $metadataSource
    ): void {
        $query = sprintf(
            'INSERT INTO %s (tenant_id, metadata_section, metadata, metadata_hash, metadata_source) ' . '
            VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE metadata=?, metadata_hash=?',
            self::TABLE
        );
        $this->db->executeQuery(
            $query,
            [
                $tenantId,
                $metadataSection,
                $metadataText,
                $metadataHash,
                $metadataSource,
                $metadataText,
                $metadataHash
            ]
        );
        static::$metadata[$tenantId][$metadataSection] = null;
    }
}
