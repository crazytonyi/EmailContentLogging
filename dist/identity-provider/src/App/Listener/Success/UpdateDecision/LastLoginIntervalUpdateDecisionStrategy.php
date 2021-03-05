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

namespace Sugarcrm\IdentityProvider\App\Listener\Success\UpdateDecision;

use Sugarcrm\IdentityProvider\Authentication\User;

/**
 * Class RealTimeStrategy
 */
class LastLoginIntervalUpdateDecisionStrategy implements UpdateDecisionStrategyInterface
{
    /**
     * Update interval in seconds
     */
    public const UPDATE_INTERVAL = 120;

    /**
     * @inheritDoc
     */
    public function needUpdate(User $user)
    {
        if (!$user->getAttribute('last_login')) {
            return true;
        }

        $utcTimeZone = new \DateTimeZone('UTC');
        $lastLogin = new \DateTime($user->getAttribute('last_login'), $utcTimeZone);
        $currentTime = new \DateTime('now', $utcTimeZone);
        return $currentTime->getTimestamp() - $lastLogin->getTimestamp() > static::UPDATE_INTERVAL;
    }
}
