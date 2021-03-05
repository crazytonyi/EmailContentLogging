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

namespace Sugarcrm\IdentityProvider\App\Provider;

use http\Exception\RuntimeException;
use League\OAuth2\Client\Provider\GenericProvider;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sugarcrm\IdentityProvider\App\Authentication\OIDCExternalService;
use Symfony\Component\Routing\Generator\UrlGenerator;

class OIDCExternalServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     */
    public function register(Container $app): void
    {
        $app['OIDCExternalService'] = function ($app) {
            $config = $app->getConfig();

            if (empty($config['oidc'])) {
                throw new RuntimeException('OIDC provider must be configured');
            }

            $oidcConfig = $config['oidc'];
            // We use generic scope separator supported by the majority of OIDC providers
            $oidcConfig['scopeSeparator'] = ' ';
            $oidcConfig['redirectUri'] =
                $app->getUrlGeneratorService()->generate('oidcCallBack', [], UrlGenerator::ABSOLUTE_URL);

            $oAuth2Provider = new GenericProvider($oidcConfig);

            return new OIDCExternalService($oAuth2Provider, $oidcConfig, $app->getSession());
        };
    }
}
