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

namespace Sugarcrm\IdentityProvider\IntegrationTests\Bootstrap;

use Behat\Gherkin\Node\TableNode;

class LocalFeatureContext extends FeatureContext
{
    /**
     * @var UserCleaner
     */
    protected $userCleaner;

    /**
     * SetUp userCleaner.
     *
     * @param array $sugarAdmin
     * @param string $screenShotPath
     */
    public function __construct(array $sugarAdmin, string $screenShotPath)
    {
        parent::__construct($sugarAdmin, $screenShotPath);
        $this->userCleaner = new UserCleaner($this, $sugarAdmin);
    }

    /**
     * @BeforeScenario @local
     */
    public function beforeLocalScenario()
    {
        $this->userCleaner->before();
    }

    /**
     * @AfterScenario @local
     */
    public function afterLocalScenario()
    {
        $this->userCleaner->clean();
    }
}
