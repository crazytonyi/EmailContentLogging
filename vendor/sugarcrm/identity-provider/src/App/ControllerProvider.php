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

namespace Sugarcrm\IdentityProvider\App;

use Silex\Application as App;
use Silex\Api\ControllerProviderInterface;
use Silex\ControllerCollection;
use Sugarcrm\IdentityProvider\App\Controller\AuthenticationController;
use Sugarcrm\IdentityProvider\App\Controller\ChangePasswordController;
use Sugarcrm\IdentityProvider\App\Controller\AdminConsentController;
use Sugarcrm\IdentityProvider\App\Controller\ConsentController;
use Sugarcrm\IdentityProvider\App\Controller\MainController;
use Sugarcrm\IdentityProvider\App\Controller\MarketingExtrasContentApiController;
use Sugarcrm\IdentityProvider\App\Controller\OIDCController;
use Sugarcrm\IdentityProvider\App\Controller\SAMLController;
use Sugarcrm\IdentityProvider\App\Controller\SetPasswordController;
use Sugarcrm\IdentityProvider\App\Controller\ForgotPasswordController;
use Sugarcrm\IdentityProvider\App\Controller\HealthController;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControllerProvider implements ControllerProviderInterface
{
    /**
     * Returns routes to connect to the given application.
     *
     * @param App $app
     * @return ControllerCollection
     */
    public function connect(App $app)
    {
        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $tenantConfigInitializer = new TenantConfigInitializer($app);
        $mainController = new MainController();
        $samlController = new SAMLController();
        $consentController = new ConsentController($app);
        $adminConsentController = new AdminConsentController($app);
        $setPasswordController = new SetPasswordController();
        $changePasswordController = new ChangePasswordController();
        $authenticationController = new AuthenticationController();
        $forgotPasswordController = new ForgotPasswordController();
        $marketingExtrasContentApiController = new MarketingExtrasContentApiController();
        $oidcController = new OIDCController();
        $healthController = new HealthController();
        $controllers->before(function (Request $request, App $app) use ($tenantConfigInitializer) {
            $exceptionRoutes = [
                'loginProcess',
                'userSelectionProcess',
                'loginRender',
                'consentInit',
                'consentCancel',
                'restAuthenticate',
                'forgotPasswordRender',
                'forgotPasswordProcess',
                'showChangePasswordForm',
                'logout',
                'samlAcs',
                'samlLogout',
                'getMarketingContentUrl',
                'healthz',
            ];
            //TODO This constraint should be more complex solution Or need to be removed.
            if (!in_array($request->attributes->get('_route'), $exceptionRoutes)) {
                try {
                    call_user_func($tenantConfigInitializer, $request);
                } catch (\RuntimeException $e) {
                    $session = $app->getSession();
                    $flashBag = $session->getFlashBag();
                    $flashBag->add('error', 'Invalid tenant ID');

                    return $app->redirect($app->getUrlGeneratorService()->generate('loginRender'));
                }
            }
        });

        $setLoggedInIdentitiesFunction = function (Request $request, Response $response, Application $app) {
            $token = $app->getRememberMeService()->retrieve();
            if ($token && $token->isAuthenticated()) {
                $cookiesService = $app->getCookieService();
                $cookiesService->setLoggedInIdentitiesCookie($response);
            }
        };

        $controllers
            ->get('/login-end-point', [$mainController, 'loginEndPointAction'])
            ->bind('loginEndPoint');

        $controllers
            ->get('/', [$mainController, 'renderFormAction'])
            ->bind('loginRender')
            ->before($app['RegionChecker']);
        $controllers
            ->post('/', [$mainController, 'postFormAction'])
            ->bind('loginProcess')
            ->after($setLoggedInIdentitiesFunction);
        $controllers
            ->post('/login-selection', [$mainController, 'postUserSelectionFormAction'])
            ->bind('userSelectionProcess');

        $controllers
            ->get('/password/forgot', [$forgotPasswordController, 'renderForgotPasswordForm'])
            ->bind('forgotPasswordRender')
            ->before($app['RegionChecker']);
        $controllers
            ->get('/password/success-sent', [$forgotPasswordController, 'successSent'])
            ->bind('forgotPasswordSuccessSent');

        $controllers
            ->post('/password/forgot', [$forgotPasswordController, 'forgotPasswordAction'])
            ->bind('forgotPasswordProcess');

        $controllers
            ->match('/logout', [$mainController, 'logoutAction'])
            ->bind('logout')
            ->before($app['RegionChecker']);

        $controllers
            ->post('/authenticate', [$authenticationController, 'authenticate'])
            ->bind('restAuthenticate');

        $controllers
            ->get('saml/logout-end-point', [$samlController, 'logoutEndPointAction'])
            ->bind('samlLogoutEndPoint');
        $controllers
            ->get('saml/login-end-point', [$samlController, 'loginEndPointAction'])
            ->bind('samlLoginEndPoint');
        $controllers
            ->get('/saml', [$samlController, 'renderFormAction'])
            ->bind('samlRender');
        $controllers
            ->get('/saml/init', [$samlController, 'initAction'])
            ->bind('samlInit');
        $controllers
            ->post('/saml/acs', [$samlController, 'acsAction'])
            ->bind('samlAcs')
            ->after($setLoggedInIdentitiesFunction);
        $controllers
            ->match('/saml/logout', [$samlController, 'logoutAction'])
            ->bind('samlLogout');
        $controllers
            ->get('/saml/logout/init', [$samlController, 'logoutInitAction'])
            ->bind('samlLogoutInit');
        $controllers
            ->get('/saml/metadata', [$samlController, 'metadataAction'])
            ->bind('samlMetaData');

        $controllers
            ->get('/consent', [$consentController, 'consentInitAction'])
            ->bind('consentInit');

        $controllers
            ->get('/consent/confirmation', [$consentController, 'consentConfirmationAction'])
            ->bind('consentConfirmation');

        $controllers
            ->get('/consent/finish', [$consentController, 'consentFinishAction'])
            ->bind('consentFinish');

        $controllers
            ->get('/consent/cancel', [$consentController, 'consentCancelAction'])
            ->bind('consentCancel');

        $controllers
            ->get('/password/set', [$setPasswordController, 'showSetPasswordForm'])
            ->bind('showSetPasswordForm');
        $controllers
            ->post('/password/set', [$setPasswordController, 'setPassword'])
            ->bind('setPassword');

        $controllers
            ->get('/password/change', [$changePasswordController, 'showChangePasswordForm'])
            ->bind('showChangePasswordForm')
            ->before([$changePasswordController, 'preCheck']);
        $controllers
            ->post('/password/change', [$changePasswordController, 'changePasswordAction'])
            ->bind('changePassword')
            ->before([$changePasswordController, 'preCheck']);

        $controllers
            ->get('/consent/admin/consent', [$adminConsentController, 'adminConsentAction'])
            ->bind('adminConsent')
            ->before([$adminConsentController, 'preCheck']);

        $controllers
            ->get('/consent/admin/consent/finish', [$adminConsentController, 'adminConsentFinishAction'])
            ->bind('adminConsentFinish')
            ->before([$adminConsentController, 'preCheck']);

        $controllers
            ->get('/consent/admin/consent/cancel', [$adminConsentController, 'adminConsentCancelAction'])
            ->bind('adminConsentCancel')
            ->before([$adminConsentController, 'preCheck']);

        $controllers
            ->get('/consent/admin/consent/remove', [$adminConsentController, 'adminConsentRemoveAction'])
            ->bind('adminConsentRemove')
            ->before([$adminConsentController, 'preCheck']);

        $controllers
            ->get('/consent/admin/consent/remove/finish', [$adminConsentController, 'adminConsentRemoveFinishAction'])
            ->bind('adminConsentRemoveFinish')
            ->before([$adminConsentController, 'preCheck']);

        $controllers
            ->get('/consent/admin/consent/remove/cancel', [$adminConsentController, 'adminConsentRemoveCancelAction'])
            ->bind('adminConsentRemoveCancel')
            ->before([$adminConsentController, 'preCheck']);

        $controllers
            ->get('/marketingContentUrl', [$marketingExtrasContentApiController, 'getMarketingContentUrlAction'])
            ->bind('getMarketingContentUrl');

        $controllers->get('/oidc/callback', [$oidcController, 'callbackAction'])
            ->bind('oidcCallBack')
            ->after($setLoggedInIdentitiesFunction);
        $controllers
            ->get('/oidc/login-endpoint', [$oidcController, 'loginEndPointAction'])
            ->bind('oidcLoginEndPoint');

        $controllers
            ->get('/healthz', [$healthController, 'healthzAction'])
            ->bind('healthz');

        return $controllers;
    }
}
