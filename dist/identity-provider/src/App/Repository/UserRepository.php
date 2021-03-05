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

use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Srn\Converter;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * Class UserRepository
 * @package Sugarcrm\IdentityProvider\App\Repository
 */
class UserRepository
{
    public const TABLE = 'users';

    /**
     * @var Connection
     */
    private $db;

    /**
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     *
     * @param string $tenantId
     * @param array $ids
     * @return string[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findActiveUserIDsForTenant(string $tenantId, array $ids): array
    {
        $idsTpl = implode(', ', array_fill(0, count($ids), '?'));
        $qr = sprintf(
            'SELECT id FROM %s WHERE tenant_id = ? AND status = ? AND id IN (' . $idsTpl . ')',
            self::TABLE
        );
        $params = array_merge([Converter::normalizeTenantId($tenantId), User::STATUS_ACTIVE], $ids);
        return $this->db->executeQuery($qr, $params)->fetchAll(FetchMode::COLUMN);
    }

    /**
     * Returns user attributes from database.
     *
     * @param string $tenantId Tenant id
     * @param string $value identity-value to to search User against
     * @param string $providerCode code of the provider user came from
     * @param int $status
     * @return array|null
     */
    public function getUserData(string $tenantId, string $value, string $providerCode, int $status = User::STATUS_ACTIVE)
    {
        $qb = $this->db->createQueryBuilder()
            ->select(
                'users.id,
                 users.tenant_id,
                 user_providers.identity_value,
                 users.password_hash,
                 users.status,
                 users.create_time,
                 users.modify_time,
                 users.created_by,
                 users.modified_by,
                 users.attributes,
                 users.custom_attributes,
                 users.last_login,
                 users.login_attempts,
                 users.password_last_changed,
                 users.lockout_time,
                 users.is_locked_out,
                 users.failed_login_attempts,
                 users.user_type'
            )
            ->from(self::TABLE)
            ->innerJoin(
                'users',
                'user_providers',
                'user_providers',
                'user_providers.user_id = users.id AND user_providers.tenant_id = users.tenant_id'
            )
            ->andWhere('users.tenant_id = :tenant_id')
            ->andWhere('users.status = :user_status')
            ->andWhere('user_providers.identity_value = :value')
            ->andWhere('user_providers.provider_code = :provider')
            ->setMaxResults(1)
            ->setParameters([
                ':value' => (string)$value,
                ':tenant_id' => $tenantId,
                ':provider' => (string)$providerCode,
                ':user_status' => $status,
            ]);

        $row = $qb->execute()->fetch(\PDO::FETCH_ASSOC);

        if (empty($row)) {
            return null;
        }

        $row['attributes'] = json_decode($row['attributes'], true);
        $row['custom_attributes'] = json_decode($row['custom_attributes'], true);
        return $row;
    }

    /**
     * Find and load User by identity-value and provider code.
     *
     * @param string $tenantId Tenant id
     * @param string $value identity-value to to search User against
     * @param string $provider code of the provider user originates from
     * @return User
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByFieldAndProvider(string $tenantId, string $value, string $provider)
    {
        $row = $this->getUserData($tenantId, $value, $provider, User::STATUS_ACTIVE);
        if (!$row) {
            throw new UsernameNotFoundException('User not found');
        }

        return new User($row['identity_value'], $row['password_hash'], $row);
    }
}
