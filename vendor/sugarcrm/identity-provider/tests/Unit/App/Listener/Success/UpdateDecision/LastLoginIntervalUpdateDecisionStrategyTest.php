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

use Sugarcrm\IdentityProvider\App\Listener\Success\UpdateDecision\LastLoginIntervalUpdateDecisionStrategy;
use Sugarcrm\IdentityProvider\Authentication\User;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Listener\Success\UpdateDecision\LastLoginIntervalUpdateDecisionStrategy
 */
class LastLoginIntervalUpdateDecisionStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     * @throws \Exception
     */
    public function needUpdateDataProvider(): array
    {
        $fiveMinutesAgo = new \DateTime('now', new \DateTimeZone('UTC'));
        $oneMinuteAgo = new \DateTime('now', new \DateTimeZone('UTC'));
        $fiveMinutesAgo->sub(new \DateInterval('PT5M'));
        $oneMinuteAgo->sub(new \DateInterval('PT1M'));
        return [
            'noLastLoginField' => [
                'user' => new User(),
                'expectedResult' => true,
            ],
            'emptyLastLoginField' => [
                'user' => new User(null, null, ['last_login' => '']),
                'expectedResult' => true,
            ],
            'lastLoginWasFiveMinutesAgo' => [
                'user' => new User(
                    null,
                    null,
                    ['last_login' => $fiveMinutesAgo->format('Y-m-d H:i:s')]
                ),
                'expectedResult' => true,
            ],
            'lastLoginWasOneMinutesAgo' => [
                'user' => new User(
                    null,
                    null,
                    ['last_login' => $oneMinuteAgo->format('Y-m-d H:i:s')]
                ),
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @param User $user
     * @param bool $expectedResult
     *
     * @covers ::needUpdate
     * @dataProvider needUpdateDataProvider
     */
    public function testNeedUpdate(User $user, bool $expectedResult): void
    {
        $strategy = new LastLoginIntervalUpdateDecisionStrategy();
        $this->assertEquals($expectedResult, $strategy->needUpdate($user));
    }
}
