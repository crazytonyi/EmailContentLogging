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

namespace Sugarcrm\IdentityProvider\App\Grpc;

use Psr\Log\LoggerInterface;
use Sugarcrm\Apis\Iam\App\V1alpha\App;
use Sugarcrm\Apis\Iam\App\V1alpha\AppAPIClient;
use Sugarcrm\Apis\Iam\App\V1alpha\ListAppsRequest;
use Sugarcrm\Apis\Iam\App\V1alpha\ListAppsResponse;

class AppService
{
    /**
     * @var AppAPIClient|null
     */
    private $appApi;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $grpcDisabled = false;

    /**
     * AppService constructor.
     * @param bool $grpcDisabled
     * @param AppAPIClient|null $appApi
     * @param LoggerInterface $logger
     */
    public function __construct(bool $grpcDisabled, ?AppAPIClient $appApi, LoggerInterface $logger)
    {
        $this->grpcDisabled = $grpcDisabled;
        $this->appApi = $appApi;
        $this->logger = $logger;
    }

    public function getCrmUrlByTenant(string $tenant): ?string
    {
        if ($this->grpcDisabled) {
            return null;
        }

        //TODO Cache
        $listAppsRequest = new ListAppsRequest();
        $listAppsRequest->setTenant($tenant);
        $listAppsRequest->setPageSize(1);
        $listAppsRequest->setFilter('type=crm');

        /** @var ListAppsResponse $response */
        [$response, $status] = $this->appApi->ListApps($listAppsRequest)->wait();
        if ($status && $status->code === \Grpc\CALL_OK) {
            $crmAppList = $response->getApps();
            if (count($crmAppList) > 0) {
                /** @var App $app */
                $crmApp = $crmAppList[0];

                $crmUrlArr = parse_url($crmApp->getRedirectUris()[0]);
                return $crmUrlArr['scheme'] . '://'
                    . $crmUrlArr['host']
                    . (isset($crmUrlArr['port']) ? ':' . $crmUrlArr['port'] : '')
                    . (isset($crmUrlArr['path']) ? $crmUrlArr['path'] : '');
            }
        } else {
            $this->logger->warning(
                sprintf(
                    'Wrong answer from APP API, code: %s, details: %s. Trying get a list of tenant crm app.',
                    $status->code,
                    $status->details
                ),
                [
                    'tenant' => $tenant,
                    'tags' => ['IdM.Grpc.AppsService'],
                ]
            );
        }

        return null;
    }

    /**
     * @param string $tenant
     * @return array|null
     */
    public function getTenantApplicationsDomains(string $tenant): array
    {
        if ($this->grpcDisabled) {
            return [];
        }

        $listAppsRequest = new ListAppsRequest();
        $listAppsRequest->setTenant($tenant);
        $listAppsRequest->setPageSize(100);
        $listAppsRequest->setFilter('type=native,crm,ua,web');
        /** @var ListAppsResponse $response */
        [$response, $status] = $this->appApi->ListApps($listAppsRequest)->wait();
        if ($status && $status->code === \Grpc\CALL_OK) {
            $result = [];
            $appList = $response->getApps();
            foreach ($appList as $app) {
                foreach ($app->getRedirectUris() as $uri) {
                    $result[] = parse_url($uri, PHP_URL_HOST);
                }
            }
            return array_values(array_unique($result));
        }

        return [];
    }
}
