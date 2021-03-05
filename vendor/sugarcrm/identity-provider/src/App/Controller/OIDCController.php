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

namespace Sugarcrm\IdentityProvider\App\Controller;

use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Authentication\ConsentRequest\ConsentToken;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Sugarcrm\IdentityProvider\Authentication\Token\OIDC\OIDCCodeToken;
use Sugarcrm\IdentityProvider\Srn\Converter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\DisabledException;

class OIDCController
{
    /**
     * @param Application $app Silex application instance.
     * @param Request $request
     * @return string
     */
    public function loginEndPointAction(Application $app, Request $request)
    {
        $redirectResponse = MainController::redirectToTenantCrm($app, $request->get('tid'));
        if (!is_null($redirectResponse)) {
            return $redirectResponse;
        }

        return $app->getTwigService()->render(
            'oidc/status.html.twig',
            [
                'user_name' => $request->get('user_name'),
                'tid' => $request->get('tid'),
                'provider' => strtoupper(Providers::OIDC),
            ]
        );
    }

    public function callbackAction(Application $app, Request $request)
    {
        if ($request->get('error')) {
            throw new AuthenticationException($request->get('error_description'));
        }

        if (!$app->getOIDCExternalService()->checkState($request->get('state'))) {
            throw new AuthenticationException('invalid state');
        }

        if (!$request->get('code')) {
            throw new AuthenticationException('code not found in OAuth2 response');
        }

        $sessionService = $app->getSession();

        try {
            $oidcToken = new OIDCCodeToken($request->get('code'));
            $token = $app->getAuthManagerService()->authenticate($oidcToken);
            if ($token->isAuthenticated()) {
                $tenant = $sessionService->get(TenantConfigInitializer::SESSION_KEY);
                $tenantSrn = Converter::fromString($tenant);
                $userIdentity = $token->getUser()->getLocalUser()->getAttribute('id');
                $userSrn = $app->getSrnManager($tenantSrn->getRegion())->createUserSrn(
                    $tenantSrn->getTenantId(),
                    $userIdentity
                );
                $user = Converter::toString($userSrn);
                $token->setAttribute('srn', $user);
                $token->setAttribute('tenantSrn', $tenant);

                $rememberMeToken = new RememberMeToken($token);
                $app->getRememberMeService()->store($rememberMeToken);
                $app->getLogger()->info(
                    'Authentication success for user {user_name} with SRN {user_srn} and {tenant} from {ip}',
                    [
                        'user_name' => $rememberMeToken->getUsername(),
                        'user_srn' => $user,
                        'tenant' => $tenant,
                        'ip' => $request->getClientIp(),
                        'tags' => ['IdM.oidc'],
                        'event' => 'after_login',
                    ]
                );

                $response = null;
                if ($sessionService->get('consent')) {
                    /** @var ConsentToken $consentToken */
                    $consentToken = $sessionService->get('consent');
                    $consentToken->setTenantSRN(Converter::toString($tenantSrn));

                    $response = $app->redirect($app->getUrlGeneratorService()->generate('consentConfirmation'));
                }

                if (is_null($response)) {
                    $urlQuery = [
                        'user_name' => $rememberMeToken->getUsername(),
                        'tid' => $sessionService->get(TenantConfigInitializer::SESSION_KEY),
                    ];

                    $response = RedirectResponse::create($app->getUrlGeneratorService()->generate(
                        'oidcLoginEndPoint',
                        $urlQuery
                    ));
                }

                $app->getCookieService()->setTenantCookie(
                    $response,
                    $sessionService->get(TenantConfigInitializer::SESSION_KEY)
                );
                $app->getCookieService()->setRegionCookie($response, $tenantSrn->getRegion());
                return $response;
            }
        } catch (DisabledException $e) {
            $app->getSession()->getFlashBag()->add('error', $e->getMessage());
            return new RedirectResponse($app->getUrlGeneratorService()->generate('loginRender'));
        } catch (AuthenticationException $e) {
            $messages[] = empty($e->getMessage()) ? $e->getMessageKey() : $e->getMessage();
            $app->getLogger()->error(
                'OIDC authentication exception occurred in tenant {tenant}',
                [
                    'tenant' => $sessionService->get(TenantConfigInitializer::SESSION_KEY),
                    'exception' => $e,
                    'tags' => ['IdM.oidc'],
                    'event' => 'login_failed',
                ]
            );
        }

        return $this->renderLoginForm($app, ['messages' => $messages]);
    }

    /**
     * @param Application $app
     * @param array $params
     * @return string
     */
    protected function renderLoginForm(Application $app, array $params = [])
    {
        $session = $app->getSession();
        $flashBag = $session->getFlashBag();

        if (empty($app['config']['oidc'])) {
            $flashBag->add('error', 'OIDC is not configured for given tenant');
        }
        if (isset($params['messages'])) {
            $flashBag->setAll(['error' => $params['messages']]);
        }

        return $app->redirect($app->getUrlGeneratorService()->generate('loginRender'));
    }
}
