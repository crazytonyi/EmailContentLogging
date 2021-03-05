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

use Sugarcrm\Apis\Iam\App\V1alpha\ListAppsResponse;
use Sugarcrm\Apis\Iam\User\V1alpha\SetPasswordRequest;
use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Constraints as CustomAssert;
use Sugarcrm\IdentityProvider\Srn\Converter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class SetPasswordController
 * @package Sugarcrm\IdentityProvider\App\Controller
 */
class SetPasswordController
{
    /**
     * Special char list
     */
    const SPECIAL_CHARS = '|}{~!@#$%^&*()_+=-';

    public const PASSWORD_REQUIREMENTS_ERROR_MESSAGE =
        'The password you entered does not meet the password requirements.';

    public const GRPC_PASSWORD_ERROR_MESSAGE =
        'Cannot change the password now. Try again later.';

    /**
     * @param Application $app
     * @param Request $request
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function showSetPasswordForm(Application $app, Request $request): string
    {
        $token = $request->get('token');
        if (!$token) {
            throw new BadRequestHttpException('Required parameters missing', null, 400);
        }

        $oneTimeTokenRepository =  $app->getOneTimeTokenRepository();
        try {
            $oneTimeTokenRepository->findUserByTokenAndTenant($token, $request->get('tid'));
        } catch (\RuntimeException $e) {
            $app->getSession()->getFlashBag()->set(
                'error',
                'You have opened a one-time password reset link that has either been used or is no longer valid. ' .
                'Please request a new one using the form below.'
            );
            return new RedirectResponse($app->getUrlGeneratorService()->generate('forgotPasswordRender'));
        }
        return $this->renderSetPasswordForm($app, $request);
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function buildPasswordCheckConstraints(Application $app): array
    {
        $translator = $app->getTranslator();
        $constraints = [
            new Assert\NotBlank(['message' => $translator->trans('Password is empty')]),
        ];
        $config = $app->getConfig();
        $passwordSettings = $config['local']['password_requirements'];
        $minMax = array_filter(
            [
                'min' => $passwordSettings['minimum_length'],
                'max' => $passwordSettings['maximum_length'],
                'minMessage' => $translator->trans(static::PASSWORD_REQUIREMENTS_ERROR_MESSAGE),
                'maxMessage' => $translator->trans(static::PASSWORD_REQUIREMENTS_ERROR_MESSAGE),
            ]
        );
        if (!empty($minMax['min']) || !empty($minMax['max'])) {
            $constraints[] = new Assert\Length($minMax);
        }

        if ($passwordSettings['require_upper']) {
            $constraints[] = new Assert\Regex([
                'pattern' => '/[A-Z]+/',
                'message' => $translator->trans(static::PASSWORD_REQUIREMENTS_ERROR_MESSAGE),
            ]);
        }

        if ($passwordSettings['require_lower']) {
            $constraints[] = new Assert\Regex([
                'pattern' => '/[a-z]+/',
                'message' => $translator->trans(static::PASSWORD_REQUIREMENTS_ERROR_MESSAGE),
            ]);
        }

        if ($passwordSettings['require_number']) {
            $constraints[] = new Assert\Regex([
                'pattern' => '/\d+/',
                'message' => $translator->trans(static::PASSWORD_REQUIREMENTS_ERROR_MESSAGE),
            ]);
        }

        if ($passwordSettings['require_special']) {
            $constraints[] = new Assert\Regex([
                'pattern' => '/[' . preg_quote(self::SPECIAL_CHARS) . ']+/',
                'message' => $translator->trans(static::PASSWORD_REQUIREMENTS_ERROR_MESSAGE),
            ]);
        }

        return $constraints;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return string
     * @throws \Throwable
     */
    public function setPassword(Application $app, Request $request): string
    {
        $translator = $app->getTranslator();
        $token = $request->get('token');
        if (!$token) {
            throw new BadRequestHttpException('Required parameters missing', null, 400);
        }

        /** @var Session $sessionService */
        $sessionService = $app->getSession();
        $flashBag = $sessionService->getFlashBag();

        $data = [
            'tid' => $request->get('tid'),
            'token' => $token,
            'newPassword' => $request->get('newPassword'),
            'confirmPassword' => $request->get('confirmPassword'),
            'csrf_token' => $request->get('csrf_token'),
        ];

        $constraint = new Assert\Collection([
            'tid' => [new Assert\NotBlank()],
            'token' => [new Assert\NotBlank()],
            'newPassword' => $this->buildPasswordCheckConstraints($app),
            'confirmPassword' => [
                new Assert\NotBlank(['message' => $translator->trans('Password confirmation is empty')]),
                new Assert\EqualTo([
                    'value' => $data['newPassword'],
                    'message' => $translator->trans('Password and password confirmation don\'t match'),
                ]),
            ],
            'csrf_token' => [new CustomAssert\Csrf($app->getCsrfTokenManager())],
        ]);

        $violations = $app->getValidatorService()->validate($data, $constraint);
        if (\count($violations) > 0) {
            $errors = array_map(
                function (ConstraintViolation $violation) {
                    return $violation->getMessage();
                },
                iterator_to_array($violations)
            );
            $app->getLogger()->debug(
                'Invalid form with errors',
                [
                    'errors' => $errors,
                    'tags' => ['IdM.password'],
                ]
            );
            $flashBag->add('error', $errors[0]);
            return $this->showSetPasswordForm($app, $request);
        }

        $tid = $request->get('tid');

        $oneTimeTokenRepository =  $app->getOneTimeTokenRepository();
        try {
            $oneTimeToken = $oneTimeTokenRepository->findUserByTokenAndTenant($token, $tid);
        } catch (\RuntimeException $e) {
            throw new BadRequestHttpException('Invalid parameters', null, 400);
        }

        $result = $this->updateUserPassword(
            $app,
            $oneTimeToken->getTenantId(),
            $oneTimeToken->getUserId(),
            $data['newPassword']
        );

        if (!$result) {
            $flashBag->add('error', static::GRPC_PASSWORD_ERROR_MESSAGE);
            return $this->showSetPasswordForm($app, $request);
        }
        $oneTimeTokenRepository->delete($oneTimeToken);

        return $app->getTwigService()->render('password/success.html.twig', []);
    }

    /**
     * Update user password
     * @param Application $app
     * @param string $id
     * @param string $tenantId
     * @param string $password
     *
     * @return bool
     */
    protected function updateUserPassword(Application $app, $tenantId, $id, $password): bool
    {
        $config = $app->getConfig();
        if ($config['grpc']['disabled']) {
            return false;
        }
        $userSrn = $app->getSrnManager($config['idm']['region'])->createUserSrn($tenantId, $id);

        $userApi = $app->getGrpcUserApi();
        $setPasswordRequest = new SetPasswordRequest();
        $setPasswordRequest->setName(Converter::toString($userSrn));
        $setPasswordRequest->setPassword($password);
        $setPasswordRequest->setSendEmail(true);
        $setPasswordRequest->setLocale($app['locale']);
        /** @var ListAppsResponse $response */
        [$response, $status] = $userApi->SetPassword($setPasswordRequest)->wait();
        return $status && $status->code === \Grpc\CALL_OK;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function renderSetPasswordForm(Application $app, Request $request): string
    {
        $config = $app->getConfig();
        $passwordRequirements = $config['local']['password_requirements'] ?? [];
        return $app->getTwigService()->render(
            'password/set.html.twig',
            [
                'tid' => $request->get('tid'),
                'token' => $request->get('token'),
                'csrf_token' => $app->getCsrfTokenManager()->getToken(CustomAssert\Csrf::FORM_TOKEN_ID),
                'passwordRequirements' => $passwordRequirements,
            ]
        );
    }
}
