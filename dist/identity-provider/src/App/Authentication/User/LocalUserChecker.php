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

namespace Sugarcrm\IdentityProvider\App\Authentication\User;

use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Authentication\User\LockoutInterface;

use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This class performs post authentication checking for Local user.
 *
 * @package Sugarcrm\IdentityProvider\Authentication\User
 */
class LocalUserChecker extends UserChecker
{
    /**
     * @var LockoutInterface
     */
    protected $lockout;

    /**
     * @param LockoutInterface $lockout
     */
    public function __construct(LockoutInterface $lockout)
    {
        $this->lockout = $lockout;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        parent::checkPreAuth($user);

        if ($user instanceof User && $this->lockout->isEnabled() && $this->lockout->isUserLocked($user)) {
            $this->lockout->throwLockoutException($user);
        }
    }
}
