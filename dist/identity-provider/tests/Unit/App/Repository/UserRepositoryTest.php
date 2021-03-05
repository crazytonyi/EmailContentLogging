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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Sugarcrm\IdentityProvider\App\Repository\UserRepository;
use Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Repository\UserRepository
 */
class UserRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $db;

    /**
     * @var ResultStatement | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $statement;

    /**
     * @var UserRepository
     */
    protected $repository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->db = $this->createMock(Connection::class);
        $this->statement = $this->createMock(ResultStatement::class);
        $this->repository = new UserRepository($this->db);
    }

    /**
     * @covers ::findActiveUserIDsForTenant
     */
    public function testFindActiveUserIDsForTenant()
    {
        $tid = '1422380696';
        $userIDs = ['user-id-1', 'user-id-2', 'user-id-3'];
        $dbUserIds = ['user-id-1', 'user-id-2'];
        $qr = 'SELECT id FROM ' . UserRepository::TABLE . ' WHERE tenant_id = ? AND status = ? AND id IN (?, ?, ?)';
        $params = [$tid, User::STATUS_ACTIVE, $userIDs[0], $userIDs[1], $userIDs[2]];

        $this->db
            ->expects($this->once())
            ->method('executeQuery')
            ->with($qr, $params)
            ->willReturn($this->statement);

        $this->statement->method('fetchAll')->willReturn($dbUserIds);

        $res = $this->repository->findActiveUserIDsForTenant($tid, $userIDs);

        $this->assertEquals($dbUserIds, $res);
    }
}
