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

namespace Sugarcrm\IdentityProvider\App\Authentication\ConfigAdapter;

class OidcConfigAdapter extends AbstractConfigAdapter
{
    /**
     * @inheritDoc
     */
    public function getConfig(string $encoded): array
    {
        $config = $this->decode($encoded);

        return [
            'urlAuthorize' => $config['authentication_endpoint'],
            'urlAccessToken' => $config['token_endpoint'],
            'urlResourceOwnerDetails' => $config['token_endpoint'],
            'urlUserInfo' => $config['userinfo_endpoint'] ?? null,
            'clientId' => $config['client_id'],
            'clientSecret' => $config['client_secret'],
            'scope' => $config['scopes'] ?? [],
            'provisionUser' => $config['provision_user'] ?? false,
        ];
    }
}
