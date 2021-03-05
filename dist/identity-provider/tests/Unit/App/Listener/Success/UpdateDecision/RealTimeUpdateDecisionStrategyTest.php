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
namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Listener\UpdateDecision;

use Sugarcrm\IdentityProvider\App\Listener\Success\UpdateDecision\RealTimeUpdateDecisionStrategy;
use Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Listener\Success\UpdateDecision\RealTimeUpdateDecisionStrategy
 */
class RealTimeUpdateDecisionStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::needUpdate
     */
    public function testNeedUpdate(): void
    {
        $user = $this->createMock(User::class);
        $strategy = new RealTimeUpdateDecisionStrategy();
        $this->assertTrue($strategy->needUpdate($user));
    }
}
