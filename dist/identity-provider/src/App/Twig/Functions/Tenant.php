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

namespace Sugarcrm\IdentityProvider\App\Twig\Functions;

use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\App\Repository\TenantRepository;
use Sugarcrm\IdentityProvider\Authentication\Tenant as TenantEntity;
use Sugarcrm\IdentityProvider\Srn;

use Symfony\Component\HttpFoundation\Session\Session;

use Twig\TwigFunction;

class Tenant extends TwigFunction
{
    private const NAME = 'tenant';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TenantRepository
     */
    private $tenantRepository;

    /**
     * @var array
     */
    private static $tenants;


    /**
     * Tenant constructor.
     * @param Session $session
     * @param TenantRepository $tenantRepository
     */
    public function __construct(Session $session, TenantRepository $tenantRepository)
    {
        parent::__construct(self::NAME, [$this, 'getTenant']);

        $this->session = $session;
        $this->tenantRepository = $tenantRepository;

        static::$tenants = [];
    }

    /**
     * @param string|null $id
     * @return TenantEntity|null
     */
    public function getTenant(string $id = null): ?TenantEntity
    {
        if ($id === null) {
            $srnString = $this->session->get(TenantConfigInitializer::SESSION_KEY);
            if (!$srnString) {
                return null;
            }

            $srn = Srn\Converter::fromString($srnString);
            $tenantId = $srn->getTenantId();
        } else {
            $tenantId = $id;
        }

        if (isset(static::$tenants[$tenantId])) {
            return static::$tenants[$tenantId];
        }

        try {
            $tenant = $this->tenantRepository->findTenantById($tenantId);
        } catch (\Exception $e) {
            return null;
        }

        static::$tenants[$tenantId] = $tenant;

        return $tenant;
    }
}
