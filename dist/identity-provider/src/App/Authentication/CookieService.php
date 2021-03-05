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

namespace Sugarcrm\IdentityProvider\App\Authentication;

use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Authentication\RememberMe\Service;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CookieService
 * @package Sugarcrm\IdentityProvider\App\Authentication
 */
class CookieService
{
    public const DEFAULT_CLEAR_TIME = 3600 * 24;

    public const DEFAULT_LIFETIME = 3600 * 24 * 365;

    public const TENANT_COOKIE_NAME = 'tid';

    public const REGION_COOKIE_NAME = 'region';

    public const SAML_TENANT_COOKIE_NAME = 'samlTid';

    public const CLOUD_UI_LANGUAGE_COOKIE_NAME = 'cuil';

    /**
     * @var string
     */
    private $localeCookieName;

    /**
     * @var string
     */
    private $identitiesCookieName;

    /**
     * @var bool
     */
    private $cookieSecure;

    /**
     * @var string
     */
    private $cookieDomain;

    /**
     * @var string
     */
    private $encryptionAlg;

    /**
     * @var string
     */
    private $encryptionKey;

    /**
     * @var Service
     */
    private $rememberMe;

    /**
     * CookieService constructor.
     * @param Application $app
     * @param string $localeCookieName
     */
    public function __construct(Application $app, string $localeCookieName)
    {
        $cookieConfig = $app['config']['cookie.options'];
        $this->cookieSecure = !empty($cookieConfig['secure']);
        $this->cookieDomain = $cookieConfig['domain'];

        $this->localeCookieName = $localeCookieName;

        $this->identitiesCookieName =
            !empty($cookieConfig['logged_cookie_name']) ? $cookieConfig['logged_cookie_name'] : 'lgi';

        if (!empty($cookieConfig['encryption_alg'])) {
            $this->encryptionAlg = $cookieConfig['encryption_alg'];
        }

        if (!empty($cookieConfig['encryption_key'])) {
            $this->encryptionKey = $cookieConfig['encryption_key'];
        }

        $this->rememberMe = $app->getRememberMeService();
    }

    /**
     * Set temporary cookie for SAML tenant
     * @param Response $response
     * @param string $value
     */
    public function setSamlTenantCookie(Response $response, string $value): void
    {
        $this->setCookie($response, self::SAML_TENANT_COOKIE_NAME, $value, 0);
    }

    /**
     * @param Response $response
     */
    public function clearSamlTenantCookie(Response $response): void
    {
        $this->clearCookie($response, self::SAML_TENANT_COOKIE_NAME);
    }

    /**
     * Set tenant cookies
     *
     * @param Response $response
     * @param string $value
     */
    public function setTenantCookie(Response $response, string $value): void
    {
        $this->setCookie($response, self::TENANT_COOKIE_NAME, $value, time() + static::DEFAULT_LIFETIME);
    }

    /**
     * Return tenant cookies
     *
     * @param Request $request
     * @return string
     */
    public function getTenantCookie(Request $request): string
    {
        return $request->cookies->get(self::TENANT_COOKIE_NAME, '');
    }

    /**
     * Set region cookies
     *
     * @param Response $response
     * @param string $value
     */
    public function setRegionCookie(Response $response, string $value): void
    {
        $this->setCookie($response, self::REGION_COOKIE_NAME, $value, time() + static::DEFAULT_LIFETIME);
    }

    /**
     * Return region cookies
     *
     * @param Request $request
     * @return string
     */
    public function getRegionCookie(Request $request): string
    {
        return $request->cookies->get(self::REGION_COOKIE_NAME, '');
    }

    /**
     * Delete region cookies
     * @todo test
     * @param Response $response
     * @return void
     */
    public function clearRegionCookie(Response $response): void
    {
        $this->clearCookie($response, self::REGION_COOKIE_NAME);
    }

    /**
     * Set locale cookies
     *
     * @param Response $response
     * @param string $value
     */
    public function setLocaleCookie(Response $response, string $value): void
    {
        $this->setCookie($response, $this->localeCookieName, $value, time() + static::DEFAULT_LIFETIME);
    }

    /**
     * Set locale cookies
     *
     * @param Response $response
     * @param string $value
     */
    public function setUICookie(Response $response, string $value): void
    {
        $this->setCookie(
            $response,
            static::CLOUD_UI_LANGUAGE_COOKIE_NAME,
            $value,
            time() + static::DEFAULT_LIFETIME
        );
    }

    /**
     * Return locale cookies
     *
     * @param Request $request
     * @return string
     */
    public function getLocaleCookie(Request $request): string
    {
        return $request->cookies->get($this->localeCookieName, '');
    }

    /**
     * @param Response $response
     */
    public function setLoggedInIdentitiesCookie(Response $response): void
    {
        $loginTokens = array_filter($this->rememberMe->list(), function (RememberMeToken $token) {
            return $token->isLoggedIn();
        });

        $loginSrns = [];
        /** @var RememberMeToken $token */
        foreach ($loginTokens as $token) {
            $loginSrns[] = $token->getSRN();
        }

        if ($loginSrns) {
            $this->setCookie(
                $response,
                $this->identitiesCookieName,
                $this->encryptData(implode('|', $loginSrns)),
                time() + static::DEFAULT_LIFETIME
            );
        } else {
            $this->clearCookie($response, $this->identitiesCookieName);
        }
    }

    /**
     * @param string $data
     * @return string
     */
    protected function encryptData(string $data): string
    {
        if (empty($this->encryptionKey) || empty($this->encryptionAlg)) {
            return $data;
        }
        $ivLen = openssl_cipher_iv_length($this->encryptionAlg);
        $iv = openssl_random_pseudo_bytes($ivLen);
        return base64_encode(
            $iv.openssl_encrypt(bin2hex($data), $this->encryptionAlg, $this->encryptionKey, OPENSSL_RAW_DATA, $iv)
        );
    }

    /**
     * @param Response $response
     * @param string $name
     * @param string $value
     * @param int $expire
     */
    protected function setCookie(
        Response $response,
        string $name,
        string $value,
        int $expire
    ): void {
        $cookie = new Cookie(
            $name,
            $value,
            $expire,
            '/',
            $this->getCookieDomain(),
            $this->cookieSecure,
            false
        );
        $response->headers->setCookie($cookie);
    }

    /**
     * @param Response $response
     * @param string $name
     */
    protected function clearCookie(Response $response, string $name): void
    {
        $cookie = new Cookie(
            $name,
            null,
            time() - static::DEFAULT_CLEAR_TIME,
            '/',
            $this->getCookieDomain(),
            $this->cookieSecure,
            false
        );
        $response->headers->setCookie($cookie);
    }

    /**
     * @return string|null
     */
    protected function getCookieDomain(): ?string
    {
        if (empty($this->cookieDomain)) {
            return null;
        }
        return '.' . ltrim($this->cookieDomain, '.');
    }
}
