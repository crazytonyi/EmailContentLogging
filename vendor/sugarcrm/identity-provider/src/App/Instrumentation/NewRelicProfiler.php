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

namespace Sugarcrm\IdentityProvider\App\Instrumentation;

/**
 * Class NewRelicProfiler
 * @package Sugarcrm\IdentityProvider\App\Instrumentation
 */
class NewRelicProfiler
{
    /** @var  bool is performance profiling is enabled? */
    private $enabled;

    /** @var  string New Relic license */
    private $license;

    /** @var  string application name that will identify this app in New Relic */
    private $appName;

    public function __construct($app)
    {
        $config = $app->getConfig();
        $this->enabled = !empty($config['newrelic']['enabled']);
        $this->license = $config['newrelic']['license'] ?? '';
        $this->appName = $config['newrelic']['appName'] ?? '';

        if ($this->enabled && !$this->isExtensionLoaded()) {
            throw new \RuntimeException('Profiling enabled but no New Relic extension found, please install');
        }

        if ($this->enabled && empty($this->license)) {
            throw new \InvalidArgumentException('Profiling enabled but no New Relic license was found');
        }

        $app->getLogger()->info('Created New Relic profiler', [
                'enabled' => $this->enabled,
            ]);
    }

    /**
     * Is profiling enabled?
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Start profiling session
     */
    public function start(): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        $this->startTransaction();
    }

    /**
     * Stop profiling session
     */
    public function stop(): void
    {
        if (!$this->isEnabled()) {
            return;
        }
        $this->endTransaction();
    }

    /**
     * Is New Relic extension loaded?
     *
     * @return bool
     */
    protected function isExtensionLoaded(): bool
    {
        return extension_loaded('newrelic') && function_exists('newrelic_set_appname');
    }

    /**
     * Start New Relic transaction
     */
    protected function startTransaction(): void
    {
        newrelic_start_transaction($this->appName, $this->license);
    }

    /**
     * End New Relic transaction
     */
    protected function endTransaction(): void
    {
        newrelic_end_transaction();
    }
}
