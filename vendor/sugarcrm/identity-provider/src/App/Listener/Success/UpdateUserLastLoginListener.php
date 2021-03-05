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

namespace Sugarcrm\IdentityProvider\App\Listener\Success;

use Sugarcrm\IdentityProvider\App\Listener\Success\UpdateDecision\UpdateDecisionStrategyInterface;
use Sugarcrm\IdentityProvider\Authentication\User;

use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class UpdateUserLastLoginListener
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var UpdateDecisionStrategyInterface
     */
    protected $updateDecision;

    /**
     * constructor
     * @param Connection $db
     * @param UpdateDecisionStrategyInterface $strategy
     */
    public function __construct(Connection $db, UpdateDecisionStrategyInterface $strategy)
    {
        $this->db = $db;
        $this->updateDecision = $strategy;
    }

    /**
     * make this class callable
     * @param AuthenticationEvent $event
     * @param string $eventName
     * @param EventDispatcherInterface $dispatcher
     */
    public function __invoke(AuthenticationEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user || !$this->updateDecision->needUpdate($user)) {
            return;
        }
        $userId = $user->getAttribute('id');
        $tenantId = $user->getAttribute('tenant_id');
        $this->db->executeUpdate(
            'UPDATE users SET last_login = ? WHERE id = ? AND tenant_id = ?',
            [(new \DateTime())->format('Y-m-d H:i:s'), $userId, $tenantId]
        );
    }

    /**
     * @param UpdateDecisionStrategyInterface $strategy
     */
    public function setUpdateDecisionStrategy(UpdateDecisionStrategyInterface $strategy): void
    {
        $this->updateDecision = $strategy;
    }
}
