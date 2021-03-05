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
use Sugarcrm\IdentityProvider\App\Constraints as CustomAssert;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Srn;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ChangePasswordController extends SetPasswordController
{
    /**
     * Pre-checks for change password
     * @param Request $request
     * @param Application $app
     * @return RedirectResponse|null
     */
    public function preCheck(Request $request, Application $app):? RedirectResponse
    {
        $redirectParams = [
            'from' => 'changePassword',
        ];
        $token = $app->getRememberMeService()->retrieve();
        if (is_null($token)) {
            if ($request->query->has('user_hint')) {
                $userHint = $request->get('user_hint');
                $redirectParams['user_hint'] = $userHint;
                $redirectParams['tenant_hint'] = $this->getTenantHintFromUserHint($app, $userHint);
            } else {
                $cookieService = $app->getCookieService();
                $tid = $cookieService->getTenantCookie($request);
                if (!empty($tid)) {
                    $redirectParams['tenant_hint'] = $tid;
                }
            }

            return $this->preCheckRedirect($app, $redirectParams);
        }

        if (!$token->getSource() instanceof UsernamePasswordToken
            || $token->getProviderKey() != Providers::PROVIDER_KEY_LOCAL) {
            $app->getSession()->getFlashBag()->add('error', 'Only local users can change password');
            return $this->preCheckRedirect($app);
        }

        if (!$token->getUser() instanceof User) {
            $app->getSession()->getFlashBag()->add('error', 'No user is found');
            return $this->preCheckRedirect($app);
        }

        if ($request->query->has('user_hint')) {
            $userHint = $request->get('user_hint');
            if ($token->getSRN() != $userHint) {
                $redirectParams['user_hint'] = $userHint;
                $redirectParams['tenant_hint'] = $this->getTenantHintFromUserHint($app, $userHint);
                return $this->preCheckRedirect($app, $redirectParams);
            }
        }

        $tenantConfigInitializer = new TenantConfigInitializer($app);
        $tenantConfigInitializer($request);

        $sessionService = $app->getSession();
        if ($sessionService->get('consent')) {
            $redirectUrl = $app->getUrlGeneratorService()->generate('loginRender');
        } else {
            $redirectUrl = $app->getRedirectURLService()->getRedirectUrl($request);
        }
        if ($sessionService->has('referer')) {
            $redirectDomain = parse_url($redirectUrl, PHP_URL_HOST);
            if ($redirectDomain !== $request->getHost()) {
                $sessionService->set('referer', $redirectUrl);
            }
        } else {
            $sessionService->set('referer', $redirectUrl);
        }

        return null;
    }

    /**
     * Derive tenant hint from user hint
     * @param Application $app
     * @param string $userHint
     * @return string
     */
    protected function getTenantHintFromUserHint(Application $app, string $userHint): string
    {
        $userSrn = Srn\Converter::fromString($userHint);
        $tenantSrn = $app->getSrnManager($app->getTenantRegion()->getRegion($userSrn->getTenantId()))
            ->createTenantSrn($userSrn->getTenantId());
        return Srn\Converter::toString($tenantSrn);
    }

    /**
     * Redirect to login form
     *
     * @param Application $app
     * @param array $params Optional query parameters
     *
     * @return RedirectResponse
     */
    protected function preCheckRedirect(Application $app, array $params = []): RedirectResponse
    {
        return $app->redirect($app->getUrlGeneratorService()->generate('loginRender', $params));
    }

    /**
     * Show change password form
     * @param Application $app
     * @param Request $request
     * @return string
     */
    public function showChangePasswordForm(Application $app, Request $request): string
    {
        return $this->renderChangePasswordForm($app);
    }

    /**
     * Change user password
     * @param Application $app
     * @param Request $request
     * @return string
     */
    public function changePasswordAction(Application $app, Request $request): string
    {
        $data = [
            'oldPassword' => $request->get('oldPassword'),
            'newPassword' => $request->get('newPassword'),
            'confirmPassword' => $request->get('confirmPassword'),
            'csrf_token' => $request->get('csrf_token'),
        ];

        $newPasswordConstraints = $this->buildPasswordCheckConstraints($app);
        $newPasswordConstraints[] = new Assert\NotEqualTo([
            'value' => $data['oldPassword'],
            'message' => 'New password must be different from previous password',
        ]);

        $constraint = new Assert\Collection([
            'oldPassword' => [new Assert\NotBlank(['message' => 'Old password is empty'])],
            'newPassword' => $newPasswordConstraints,
            'confirmPassword' => [
                new Assert\NotBlank(['message' => 'Password confirmation is empty']),
                new Assert\EqualTo([
                    'value' => $data['newPassword'],
                    'message' => 'New passwords do not match.',
                ]),
            ],
            'csrf_token' => [new CustomAssert\Csrf($app->getCsrfTokenManager())],
        ]);

        $violations = $app->getValidatorService()->validate($data, $constraint);
        if (count($violations)) {
            $errors = array_map(function (ConstraintViolationInterface $violation) {
                return $violation->getMessage();
            }, iterator_to_array($violations));
            $app->getSession()->getFlashBag()->set('error', $errors[0]);
            return $this->renderChangePasswordForm($app);
        }
        /** @var UsernamePasswordToken $token */
        $token = $app->getRememberMeService()->retrieve();
        /** @var User $user */
        $user = $token->getUser();

        $encoder = $app->getEncoderFactory()->getEncoder(User::class);
        if (!$encoder->isPasswordValid($user->getAttribute('password_hash'), $data['oldPassword'], '')) {
            $app->getSession()->getFlashBag()->set('error', 'Old password is not valid');
            return $this->renderChangePasswordForm($app);
        }
        $tenantSrn = Srn\Converter::fromString($token->getAttribute('tenantSrn'));

        $result = $this->updateUserPassword(
            $app,
            $tenantSrn->getTenantId(),
            $user->getAttribute('id'),
            $data['newPassword']
        );

        if (!$result) {
            $app->getSession()->getFlashBag()->set('error', static::GRPC_PASSWORD_ERROR_MESSAGE);
            return $this->renderChangePasswordForm($app);
        }

        $this->refreshUser($app, $tenantSrn, $user, $token->getSource());
        $sessionService = $app->getSession();
        $redirectUrl = $sessionService->get('referer') ??
            $app->getUrlGeneratorService()->generate('loginRender');
        $sessionService->remove('referer');
        return $app->getTwigService()->render(
            'password/success.change.html.twig',
            ['redirectUrl' => $redirectUrl]
        );
    }

    /**
     * Render change password form
     * @param Application $app
     * @return string
     */
    protected function renderChangePasswordForm(Application $app)
    {
        $config = $app->getConfig();
        $passwordRequirements = $config['local']['password_requirements'] ?? [];
        $redirectUrl = $app->getSession()->get('referer') ??
            $app->getUrlGeneratorService()->generate('loginRender');
        return $app->getTwigService()->render(
            'password/change.html.twig',
            [
                'csrf_token' => $app->getCsrfTokenManager()->getToken(CustomAssert\Csrf::FORM_TOKEN_ID),
                'passwordRequirements' => $passwordRequirements,
                'redirectUrl' => $redirectUrl,
            ]
        );
    }

    /**
     * refresh user in stored token
     * @param Application $app
     * @param Srn\Srn $tenant
     * @param User $user
     * @param UsernamePasswordToken $token
     */
    protected function refreshUser(Application $app, Srn\Srn $tenant, User $user, UsernamePasswordToken $token): void
    {
        $localProvider = $app->getUserProviderBuilder()->build($tenant, Providers::PROVIDER_KEY_LOCAL);
        $user = $localProvider->refreshUser($user);
        $token->setUser($user);

        $rememberMeToken = new RememberMeToken($token);
        $app->getRememberMeService()->store($rememberMeToken);
    }
}
