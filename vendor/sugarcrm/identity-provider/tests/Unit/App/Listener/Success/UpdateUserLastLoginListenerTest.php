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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Listener\Success;

use Sugarcrm\IdentityProvider\App\Listener\Success\UpdateDecision\UpdateDecisionStrategyInterface;
use Sugarcrm\IdentityProvider\App\Listener\Success\UpdateUserLastLoginListener;
use Sugarcrm\IdentityProvider\Authentication\User;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Listener\Success\UpdateUserLastLoginListener
 */
class UpdateUserLastLoginListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection | \PHPUnit_Framework_MockObject_MockObject
     */
    private $dbConnection;

    /**
     * @var AuthenticationEvent | \PHPUnit_Framework_MockObject_MockObject
     */
    private $event;

    /**
     * @var EventDispatcher | \PHPUnit_Framework_MockObject_MockObject
     */
    private $dispatcher;

    /**
     * @var UsernamePasswordToken | \PHPUnit_Framework_MockObject_MockObject
     */
    private $token;

    /**
     * @var UpdateDecisionStrategyInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $updateDecision;

    protected function setUp()
    {
        $this->dbConnection = $this->createMock(Connection::class);
        $this->event = $this->createMock(AuthenticationEvent::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->token = $this->createMock(UsernamePasswordToken::class);
        $this->updateDecision = $this->createMock(UpdateDecisionStrategyInterface::class);


        $this->event->method('getAuthenticationToken')->willReturn($this->token);

        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $user = new User('max', 'max', ['id' => 'max-id', 'tenant_id' => '1234567890']);
        $this->token->method('getUser')->willReturn($user);
        $this->updateDecision->expects($this->once())
            ->method('needUpdate')
            ->with($user)
            ->willReturn(true);

        $this->dbConnection->expects($this->once())
            ->method('executeUpdate')
            ->with(
                'UPDATE users SET last_login = ? WHERE id = ? AND tenant_id = ?',
                $this->callback(function ($params) {
                    $this->assertCount(3, $params);
                    $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $params[0]);
                    $this->assertEquals('max-id', $params[1]);
                    $this->assertEquals('1234567890', $params[2]);
                    return true;
                })
            );

        $listener = new UpdateUserLastLoginListener($this->dbConnection, $this->updateDecision);
        $listener($this->event, 'success', $this->dispatcher);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithNoNeedUpdateUser()
    {
        $user = new User('max', 'max', ['id' => 'max-id', 'tenant_id' => '1234567890']);
        $this->token->method('getUser')->willReturn($user);
        $this->updateDecision->expects($this->once())
            ->method('needUpdate')
            ->with($user)
            ->willReturn(false);

        $this->dbConnection->expects($this->never())->method('executeUpdate');

        $listener = new UpdateUserLastLoginListener($this->dbConnection, $this->updateDecision);
        $listener($this->event, 'success', $this->dispatcher);
    }
}
