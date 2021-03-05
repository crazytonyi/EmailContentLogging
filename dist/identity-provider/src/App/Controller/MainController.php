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
use Sugarcrm\IdentityProvider\App\Repository\Exception\TenantInDifferentRegionException;
use Sugarcrm\IdentityProvider\App\Repository\Exception\TenantNotActiveException;
use Sugarcrm\IdentityProvider\App\Repository\Exception\TenantNotExistsException;
use Sugarcrm\IdentityProvider\App\Constraints as CustomAssert;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeTokenInterface;
use Sugarcrm\IdentityProvider\Srn;
use Sugarcrm\IdentityProvider\Srn\Converter as SRNConverter;
use Sugarcrm\IdentityProvider\Authentication\Token\SAML\ResultToken as SAMLResultToken;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class MainController.
 */
class MainController
{
    protected static $invalidCredentials = 'Invalid credentials';
    /**
     * @param Application $app Silex application instance.
     * @param Request $request
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function loginEndPointAction(Application $app, Request $request)
    {
        $redirectResponse = self::redirectToTenantCrm($app, $request->get('tid'));
        if (!is_null($redirectResponse)) {
            return $redirectResponse;
        }

        if (Providers::PROVIDER_KEY_SAML === $request->get('provider')) {
            return new RedirectResponse(
                $app->getUrlGeneratorService()->generate('samlLoginEndPoint', $request->query->all())
            );
        }

        $providersTitle = [
            Providers::PROVIDER_KEY_LOCAL => 'Local',
            Providers::PROVIDER_KEY_LDAP => 'LDAP',
        ];
        $params = [
            'tid' => $request->get('tid'),
            'user_name' => $request->get('user_name'),
            'provider' => $providersTitle[$request->get('provider')],
        ];
        $app->getLogger()->debug('Successfully authentication status page render', [
            'params' => $params,
            'tags' => ['IdM.main'],
        ]);
        return $app->getTwigService()->render('main/status.html.twig', $params);
    }

    /**
     * Redirect user to crm if it provisioned
     *
     * @param Application $app
     * @param string $tid
     * @return RedirectResponse|null
     */
    public static function redirectToTenantCrm(Application $app, string $tid): ?RedirectResponse
    {
        if (empty($tid)) {
            return null;
        }

        $crmUrl = $app->getGrpcAppService()->getCrmUrlByTenant($tid);

        if (empty($crmUrl)) {
            return null;
        }

        $app->getLogger()->debug(
            'Redirecting to crm authenticated user',
            [
                'tid' => $tid,
                'crmUrl' => $crmUrl,
                'tags' => ['IdM.main'],
            ]
        );
        return new RedirectResponse($crmUrl);
    }

    /**
     * @param Application $app Silex application instance.
     * @param Request $request
     * @return string
     */
    public function renderFormAction(Application $app, Request $request)
    {
        $session = $app->getSession();
        if ($request->query->has('from')) {
            $session->set('from', $request->get('from'));
            if ($request->query->has('user_hint')) {
                $session->set('fromParams', ['user_hint' => $request->get('user_hint')]);
            }
        }
        $rememberMeService = $app->getRememberMeService();
        $activeToken = $rememberMeService->retrieve();
        $allTokens = $this->getAllTokens($app);
        $params = [
            'tid' => '',
            'user_name' => '',
            'ssoLogin' => false,
        ];

        $requestTenantHint = $request->query->get(TenantConfigInitializer::REQUEST_KEY);
        $requestUserHint = $request->query->get('user_hint');
        $loggedInTokens = array_filter($allTokens, function ($tok) {
            return $tok->isLoggedIn();
        });
        $isRequestForNewTenant = isset($requestTenantHint) &&
            !$this->isTenantInTokens($requestTenantHint, $allTokens);
        $tenantTokens = $this->filterTokenByTenant($requestTenantHint, $loggedInTokens);

        // If we have this special parameter and no tenant hint:
        // 1) we need to clear tenant in session to show generic Sugar logo
        // 2) redirect to the tenant screen.
        $newLoginMarker = $request->get('new_login');
        if ($newLoginMarker && !$requestTenantHint) {
            $session->remove(TenantConfigInitializer::SESSION_KEY);
            $params['new_login'] = $newLoginMarker;
            return $this->renderTenantForm($app, $params);
        }

        if (
            count($allTokens) > 1
            && count($tenantTokens) != 1
            && !$isRequestForNewTenant
            && !$requestUserHint
            && !$newLoginMarker
        ) {
            $rememberMeService->refreshUserData();
            return $this->renderUserSelectionForm($app, $allTokens);
        }

        if (
            !$newLoginMarker && count($tenantTokens) === 1 &&
            (!$requestUserHint || $requestUserHint === $tenantTokens[0]->getSrn())
        ) {
            $app->getRememberMeService()->store($tenantTokens[0]);
            $activeToken = $app->getRememberMeService()->retrieve();
        }
        if ($activeToken &&
            !$newLoginMarker &&
            (!$requestTenantHint || $this->isTenantInTokens($requestTenantHint, [$activeToken])) &&
            (!$requestUserHint || $requestUserHint === $activeToken->getSrn())) {
            $session->set(TenantConfigInitializer::SESSION_KEY, $activeToken->getAttribute('tenantSrn'));
            return $this->redirectAuthenticatedUser($app, $request, $activeToken);
        }

        $tenantConfigInitializer = new TenantConfigInitializer($app);

        if ($request->query->has('login_hint')) {
            $params['user_name'] = $request->query->get('login_hint');
        }

        // If no tenant found we show the screen with tenant form.
        if (!$tenantConfigInitializer->hasTenant($request)) {
            $tid = $app->getCookieService()->getTenantCookie($request);
            if (!empty($tid)) {
                $tenant = Srn\Converter::fromString($tid);
                $params['tid'] = $tenant->getTenantId();
            }
            return $this->renderTenantForm($app, $params);
        }

        // Tenant was found so we try go to the screen with SSO/login-form.
        try {
            // try handle invalid tenant hint
            $tenantConfigInitializer->initConfig($request);
        } catch (TenantNotExistsException $e) {
            $app->getLogger()->info('Invalid tenant id', [
                'errors' => $e->getMessage(),
                'tags' => ['IdM.main'],
            ]);
            return $this->processNotExistsTenant($app, $e->getMessage());
        } catch (TenantNotActiveException $e) {
            $app->getLogger()->info('Inactive tenant id', [
                'errors' => $e->getMessage(),
                'tags' => ['IdM.main'],
            ]);
            return $this->processInactiveTenant($app, $e->getMessage());
        } catch (TenantInDifferentRegionException $e) {
            $app->getLogger()->debug('Different region, redirecting', [
                'region' => $e->getTenantRegion(),
                'tenant' => $e->getTenantId(),
                'errors' => $e->getMessage(),
                'tags' => ['IdM.main'],
            ]);
            return $app->getRegionChecker()->redirectToRegion(
                $request,
                $e->getTenantRegion(),
                $e->getTenantId()
            );
        }

        $config = $app->getConfig();

        $tenant = Srn\Converter::fromString($session->get(TenantConfigInitializer::SESSION_KEY));
        $params['tid'] = $tenant->getTenantId();

        if (!empty($config['saml']) || !empty($config['oidc'])) {
            $params['ssoLogin'] = true;
            $params['ssoLoginUrl'] = $this->getSSOLoginUrl($app);
        }
        return $this->renderLoginForm($app, $params);
    }

    /**
     * @param Application $app Silex application instance.
     * @param Request $request
     * @return string
     */
    public function postFormAction(Application $app, Request $request)
    {
        /** @var Session $sessionService */
        $sessionService = $app->getSession();
        $flashBag = $sessionService->getFlashBag();

        // collect data
        $data = [
            'tid' => $request->get('tid'),
            'user_name' => $request->get('user_name'),
            'password' => $request->get('password'),
            'csrf_token' => $request->get('csrf_token'),
        ];

        $dataToLog = $data;
        $dataToLog['password'] = '***obfuscated***';

        $app->getLogger()->debug('Validation form data', [
            'data' => $dataToLog,
            'tags' => ['IdM.main'],
        ]);
        $constraint = new Assert\Collection([
            'tid' => [new Assert\NotBlank()],
            'user_name' => [new Assert\NotBlank()],
            'password' => [new Assert\NotBlank()],
            'csrf_token' => [new CustomAssert\Csrf($app->getCsrfTokenManager())],
        ]);
        $violations = $app->getValidatorService()->validate($data, $constraint);
        if (count($violations) > 0) {
            $errors = array_map(function (ConstraintViolation $violation) {
                return $violation->getMessage();
            }, iterator_to_array($violations));
            $app->getLogger()->debug('Invalid form with errors', [
                'errors' => $errors,
                'tags' => ['IdM.main'],
            ]);
            $flashBag->add('error', 'All fields are required');
            return new RedirectResponse($app->getUrlGeneratorService()->generate('loginRender', [
                TenantConfigInitializer::REQUEST_KEY => $data['tid'],
                'login_hint' => $data['user_name'],
            ]));
        }

        try {
            $tenantConfigInitializer = new TenantConfigInitializer($app);
            $tenantConfigInitializer($request);

            $token = $app->getUsernamePasswordTokenFactory(
                $data['user_name'],
                $data['password']
            )->createAuthenticationToken();

            $userName = $token->getUsername();
            $tenant = $sessionService->get(TenantConfigInitializer::SESSION_KEY);

            $app->getLogger()->info('Trying to authenticate user:{user_name} in tenant:{tid}', [
                'user_name' => $userName,
                'tid' => $tenant,
                'tags' => ['IdM.main'],
            ]);

            $token = $app->getAuthManagerService()->authenticate($token);

            if (!empty($token) && $token->isAuthenticated()) {
                $tenantSrn = Srn\Converter::fromString($tenant);
                $userIdentity = $token->getUser()->getLocalUser()->getAttribute('id');
                $userSrn = $app->getSrnManager($tenantSrn->getRegion())
                    ->createUserSrn($tenantSrn->getTenantId(), $userIdentity);
                $user = Srn\Converter::toString($userSrn);
                $token->setAttribute('srn', $user);
                $token->setAttribute('tenantSrn', $tenant);

                $rememberMeToken = new RememberMeToken($token);
                $app->getRememberMeService()->store($rememberMeToken);

                $app->getLogger()->info(
                    'Authentication success for user {user_name} with SRN {user_srn} and {tenant} from {ip}',
                    [
                        'user_name' => $userName,
                        'user_srn' => $user,
                        'tenant' => $tenant,
                        'ip' => $request->getClientIp(),
                        'tags' => ['IdM.main'],
                        'event' => 'after_login',
                    ]
                );

                return $this->redirectAuthenticatedUser($app, $request, $rememberMeToken);
            }
        } catch (BadCredentialsException $e) {
            $flashBag->add('error', static::$invalidCredentials);

            $app->getLogger()->notice(
                'Bad credentials occurred for user:{user_name} with SRN {user_srn} in tenant:{tid}',
                [
                    'user_name' => $data['user_name'],
                    'user_srn' => $user ?? 'unknown',
                    'tid' => $data['tid'],
                    'tags' => ['IdM.main'],
                    'event' => 'login_failed',
                ]
            );
        } catch (AuthenticationException $e) {
            $message = empty($e->getMessage()) ? $e->getMessageKey() : $e->getMessage();
            $flashBag->add('error', $message);

            $app->getLogger()->warning(
                'Authentication Exception occurred for user:{user_name} with SRN {user_srn} in tenant:{tid}',
                [
                    'user_name' => $data['user_name'],
                    'user_srn' => $user ?? 'unknown',
                    'tid' => $data['tid'],
                    'exception' => $e,
                    'tags' => ['IdM.main'],
                    'event' => 'login_failed',
                ]
            );
        } catch (\InvalidArgumentException $e) {
            $flashBag->add('error', static::$invalidCredentials);

            $app->getLogger()->warning(
                'User:{user_name} with SRN {user_srn} try login with invalid tenant:{tid}',
                [
                    'user_name' => $data['user_name'],
                    'user_srn' => $user ?? 'unknown',
                    'tid' => $data['tid'],
                    'exception' => $e,
                    'tags' => ['IdM.main'],
                    'event' => 'login_failed',
                ]
            );
        } catch (\RuntimeException $e) {
            $flashBag->add('error', static::$invalidCredentials);

            $app->getLogger()->warning(
                'User:{user_name} with SRN {user_srn} try login with not existing tenant:{tid}',
                [
                    'user_name' => $data['user_name'],
                    'user_srn' => $user ?? 'unknown',
                    'tid' => $data['tid'],
                    'exception' => $e,
                    'tags' => ['IdM.main'],
                    'event' => 'login_failed',
                ]
            );
        } catch (\Exception $e) {
            $flashBag->add('error', 'APP ERROR: ' . $e->getMessage());

            $app->getLogger()->error(
                'Exception occurred for user:{user_name} with SRN {user_srn} in tenant:{tid}',
                [
                    'user_name' => $data['user_name'],
                    'user_srn' => $user ?? 'unknown',
                    'tid' => $sessionService->get(TenantConfigInitializer::SESSION_KEY),
                    'exception' => $e,
                    'tags' => ['IdM.main'],
                    'event' => 'login_failed',
                ]
            );
        }

        return new RedirectResponse($app->getUrlGeneratorService()->generate('loginRender', [
            TenantConfigInitializer::REQUEST_KEY => $sessionService->get(TenantConfigInitializer::SESSION_KEY),
            'login_hint' => $data['user_name'],
        ]));
    }

    /**
     * @param Application $app Silex application instance.
     * @param Request $request
     * @return string
     */
    public function postUserSelectionFormAction(Application $app, Request $request)
    {
        // Collect data and perform validation
        $data = [
            'tid' => $request->get('tid'),
            'user_srn' => $request->get('user_srn'),
            'user_name' => $request->get('user_name'),
            'csrf_token' => $request->get('csrf_token'),
        ];
        $app->getLogger()->debug('Processing of user selection form data', [
            'data' => $data,
            'tags' => ['IdM.main'],
        ]);
        $constraint = new Assert\Collection([
            'tid' => [new Assert\NotBlank()],
            'user_srn' => [new Assert\NotBlank()],
            'user_name' => [new Assert\NotBlank()],
            'csrf_token' => [new CustomAssert\Csrf($app->getCsrfTokenManager())],
        ]);
        $violations = $app->getValidatorService()->validate($data, $constraint);
        if (count($violations) > 0) {
            $errors = array_map(function (ConstraintViolation $violation) {
                return $violation->getMessage();
            }, iterator_to_array($violations));
            $app->getLogger()->debug('Invalid form with errors', [
                'errors' => $errors,
                'tags' => ['IdM.main'],
            ]);
            $sessionService = $app->getSession();
            $flashBag = $sessionService->getFlashBag();
            $flashBag->add('error', 'All fields are required');
            return new RedirectResponse($app->getUrlGeneratorService()->generate('loginRender'));
        }

        $logData = [
            'user_srn' => $data['user_srn'],
            'user_name' => $data['user_name'],
            'tenant' => $data['tid'],
            'ip' => $request->getClientIp(),
            'tags' => ['IdM.main'],
            'event' => 'after_login',
        ];

        // try to find selected user among tokens.
        $token = $app->getRememberMeService()->retrieveBySrn($data['user_srn']);
        if ($token && $token->isLoggedIn()) {
            $app->getLogger()->info(
                'Activating logged-in session for user {user_name} with SRN {user_srn} in tenant {tid} from {ip}',
                $logData
            );
            $app->getRememberMeService()->store($token);
            return $this->redirectAuthenticatedUser($app, $request, $token);
        }

        $app->getLogger()->info(
            'Redirecting logged-out user {user_name} with SRN {user_srn} in tenant {tid} from {ip} to Login page',
            $logData
        );
        return new RedirectResponse($app->getUrlGeneratorService()->generate('loginRender', [
            TenantConfigInitializer::REQUEST_KEY => $data['tid'],
            'login_hint' => $data['user_name'],
            'tenant_hint' => $data['tid'],
            'user_hint' => $data['user_srn'],
        ]));
    }

    /**
     * LogOut action
     * @param Application $app
     * @param Request $request
     * @return RedirectResponse
     */
    public function logoutAction(Application $app, Request $request): RedirectResponse
    {
        if ($userHint = $request->get('user_hint', null)) {
            $token = $app->getRememberMeService()->retrieveBySrn($userHint);
        } else {
            $token = $app->getRememberMeService()->retrieve();
        }
        if ($token && $token->getSource() instanceof SAMLResultToken) {
            $user = $token->getUser();
            $url = $app->getUrlGeneratorService()->generate(
                'samlLogoutInit',
                [
                    'nameId' => $user->getAttribute('identityValue'),
                    'redirect_uri' => $app->getRedirectURLService()->getRedirectUrl($request),
                    'sessionIndex' => $token->getAttribute('IdPSessionIndex'),
                ]
            );
            return RedirectResponse::create($url);
        }
        $response = $app->getLogoutService()->logout($request, $request->get('user_hint', null));

        if (!is_null($token)) {
            $app->getLogger()->info('Logout for user {user_name} with SRN {user_srn}', [
                'user_name' => $token->getUsername(),
                'user_srn' => $token->hasAttribute('srn') ? $token->getAttribute('srn') : 'unknown',
                'tags' => ['IdM.main'],
                'event' => 'after_logout',
            ]);
        }
        $app->getCookieService()->clearRegionCookie($response);

        return $response;
    }

    /**
     * Redirect user to consent or landing page
     *
     * @param Application $app
     * @param Request $request
     * @param RememberMeTokenInterface $token Authenticated result token
     * @return RedirectResponse
     */
    protected function redirectAuthenticatedUser(
        Application $app,
        Request $request,
        RememberMeTokenInterface $token
    ): RedirectResponse {
        $sessionService = $app->getSession();
        $from = $sessionService->get('from');
        $fromParams = $sessionService->get('fromParams', []);
        $sessionService->remove('from');
        $sessionService->remove('fromParams');
        if ($from == 'changePassword' || $app->getUserPasswordChecker()->isPasswordExpired($token->getSource())) {
            return $app->redirect($app->getUrlGeneratorService()->generate('showChangePasswordForm', $fromParams));
        }

        $response = null;
        $route = '';
        if ($sessionService->get('consent')) {
            /** @var ConsentToken $consentToken */
            $consentToken = $sessionService->get('consent');
            $consentToken->setTenantSRN($token->getAttribute('tenantSrn'));

            $response = $app->redirect($app->getUrlGeneratorService()->generate('consentConfirmation'));
            $route = 'consentConfirmation';
        }

        if ($token->hasAttribute('tenantSrn')) {
            $tid = $token->getAttribute('tenantSrn');
        } else {
            $tid = $sessionService->get(TenantConfigInitializer::SESSION_KEY);
        }

        if (is_null($response)) {
            $route = 'loginEndPoint';
            $response = RedirectResponse::create($app->getUrlGeneratorService()->generate(
                'loginEndPoint',
                [
                    'tid' => $tid,
                    'user_name' => $token->getUsername(),
                    'provider' => $token->getProviderKey(),
                ]
            ));
        }

        $app->getLogger()->debug('Redirect user:{user_name} with SRN {user_srn} in tenant:{tid} to route:{route}', [
            'user_name' => $token->getUsername(),
            'user_srn' => $token->getAttribute('srn'),
            'tid' => $tid,
            'route' => $route,
            'tags' => ['IdM.main'],
        ]);
        $app->getCookieService()->setTenantCookie(
            $response,
            $tid
        );

        $tenantSrn = Srn\Converter::fromString($tid);
        $app->getCookieService()->setRegionCookie($response, $tenantSrn->getRegion());
        return $response;
    }

    /**
     * @param Application $app
     * @param $error
     * @return string|RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function processNotExistsTenant(Application $app, $error)
    {
        $session = $app->getSession();
        $session->getFlashBag()->add('error', $error);

        return $this->renderTenantForm($app, ['user_name' => '']);
    }

    /**
     * @param Application $app
     * @param $error
     * @return string|RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function processInactiveTenant(Application $app, $error)
    {
        $session = $app->getSession();
        $session->getFlashBag()->add('error', $error);
        if ($session->get('consent')) {
            return $app->redirect($app->getUrlGeneratorService()->generate('consentCancel'));
        }

        return $this->renderTenantForm($app, ['user_name' => '']);
    }

    /**
     * @param Application $app
     * @param array $params
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function renderTenantForm(Application $app, array $params = []): string
    {
        $app->getLogger()->debug('Render tenant form', [
            'params' => $params,
            'tags' => ['IdM.main'],
        ]);

        if (!isset($params['tid'])) {
            $params['tid'] = '';
        }

        return $app->getTwigService()->render('main/tenant.html.twig', $params);
    }

    /**
     * @param Application $app
     * @param array $params
     * @return string
     */
    protected function renderLoginForm(Application $app, array $params = [])
    {
        $app->getLogger()->debug('Render login form', [
            'params' => $params,
            'tags' => ['IdM.main'],
        ]);
        $params = array_merge($params, [
            'csrf_token' => $app->getCsrfTokenManager()->getToken(CustomAssert\Csrf::FORM_TOKEN_ID)
        ]);
        return $app->getTwigService()->render('main/login.html.twig', $params);
    }

    /**
     * @param Application $app
     * @param array $params
     * @return string
     */
    protected function renderUserSelectionForm(Application $app, $tokens, array $params = [])
    {
        $params['tokensInfo'] = array_map(
            function ($token) {
                $localUser = $token->getUser()->getLocalUser();
                $fullName = $localUser ? $localUser->getFullname() : null;
                return [
                    'name' => empty($fullName) ? $token->getUser()->getUsername() : $fullName,
                    'userName' => $token->getUser()->getUsername(),
                    'userSrn' => $token->getSRN(),
                    'loggedIn' => $token->isLoggedIn(),
                    'tenantID' => $token->getTenantId(),
                ];
            },
            $tokens
        );

        /** @var ConsentToken $consentToken */
        $consentToken = $app->getSession()->get('consent');
        if ($consentToken) {
            $clientId = $consentToken->getClientId();
            try {
                /** @var Srn\Srn $srn */
                $srn = SRNConverter::fromString($clientId);
                if ($srn && Srn\Manager::isCrm($srn)) {
                    $params['tokensInfo'] = array_filter(
                        $params['tokensInfo'],
                        static function ($token) use ($srn) {
                            return $srn->getTenantId() === $token['tenantID'];
                        }
                    );
                }
            } catch (\InvalidArgumentException $e) {
            }
        }

        $app->getLogger()->debug('Render user selection form', [
            'params' => $params,
            'tags' => ['IdM.main'],
        ]);
        $params = array_merge($params, [
            'csrf_token' => $app->getCsrfTokenManager()->getToken(CustomAssert\Csrf::FORM_TOKEN_ID)
        ]);
        return $app->getTwigService()->render('main/user.selection.html.twig', $params);
    }

    /**
     * Filter token by tenant(SRN or ID)
     * @param $tenantHint
     * @param array $tokens
     * @return array
     */
    private function filterTokenByTenant($tenantHint, array $tokens): array
    {
        try {
            $tenantSrn =  Srn\Converter::fromString($tenantHint);
            $tenantId = $tenantSrn->getTenantId();
        } catch (\InvalidArgumentException $e) {
            $tenantId = $tenantHint;
        }
        $tenantTokens = [];
        foreach ($tokens as $tok) {
            if ($tok->hasAttribute('tenantSrn') && $tok->getAttribute('tenantSrn') === $tenantHint ||
                $tok->getTenantId() === $tenantId) {
                $tenantTokens[] = $tok;
            }
        }
        return  $tenantTokens;
    }

    /**
     * Is the tenant (SRN or ID) is the tenant of any token from the list?
     * @param string $tenantHint
     * @param array $tokens
     * @return bool
     */
    protected function isTenantInTokens($tenantHint, array $tokens)
    {
        return count($this->filterTokenByTenant($tenantHint, $tokens)) >= 1;
    }

    /**
     * Get all tokens in session for active users
     *
     * @param Application $app
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getAllTokens(Application $app): array
    {
        $tenantUserIDs = [];
        foreach ($app->getRememberMeService()->list() as $token) {
            try {
                $tid = $token->getTenantId();
                [$type, $userID] = SRNConverter::fromString($token->getSRN())->getResource();
                $tenantUserIDs[$tid][] = $userID;
            } catch (\Exception $e) {
                $app->getRememberMeService()->remove($token);
                $app->getLogger()->warning('Token without valid user SRN', [
                    'violation' => $e->getMessage(),
                    'invalidSRN' => $token->getSRN(),
                ]);
            }
        }

        foreach ($tenantUserIDs as $tid => $userIDs) {
            $tid = (string)$tid;
            $dbUserIDs = $app->getUserRepository()->findActiveUserIDsForTenant($tid, $userIDs);
            $removeUserIDs = array_diff($userIDs, $dbUserIDs);
            foreach ($removeUserIDs as $removeUserID) {
                $removeSRN = $app->getSrnManager($app->getConfig()['idm']['region'])
                    ->createUserSrn($tid, $removeUserID);
                $removeToken = $app->getRememberMeService()->retrieveBySrn(SRNConverter::toString($removeSRN));
                if (!is_null($removeToken)) {
                    $app->getRememberMeService()->remove($removeToken);
                }
            }
        }

        $app->getRememberMeService()->refreshUserData();

        return $app->getRememberMeService()->list();
    }

    /**
     * @param Application $app
     * @return string
     */
    protected function getSSOLoginUrl(Application $app): string
    {
        $config = $app->getConfig();
        if (!empty($config['saml'])) {
            return $app->getUrlGeneratorService()->generate('samlInit', []);
        }
        if (!empty($config['oidc'])) {
            return $app->getOIDCExternalService()->getAuthorizationUrl();
        }

        return '';
    }
}
