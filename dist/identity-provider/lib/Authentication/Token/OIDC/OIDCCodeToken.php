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
namespace Sugarcrm\IdentityProvider\Authentication\Token\OIDC;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OIDCCodeToken extends AbstractToken
{

    /**
     * @var string
     */
    private $code;

    /**
     * @inheritDoc
     */
    public function __construct(string $code, array $roles = [])
    {
        $this->code = $code;
        parent::__construct($roles);
    }

    /**
     * @inheritDoc
     */
    public function getCredentials(): string
    {
        return $this->code;
    }
}
