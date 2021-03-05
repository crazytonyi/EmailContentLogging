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

use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use League\OAuth2\Client\Token\AccessToken;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Session\Session;

class OIDCExternalService
{
    public const STATE_KEY = 'oidcState';
    /**
     * @var OAuth2Provider
     */
    private $oAuth2Provider;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Session
     */
    private $session;

    /**
     * OIDCExternalService constructor.
     * @param OAuth2Provider $oAuth2Provider
     * @param array $config
     * @param Session $session
     */
    public function __construct(OAuth2Provider $oAuth2Provider, array $config, Session $session)
    {
        $this->oAuth2Provider = $oAuth2Provider;
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function getAuthorizationUrl(): string
    {
        $state = Uuid::uuid4()->toString();
        $this->session->set(static::STATE_KEY, $state);
        $url = $this->oAuth2Provider->getAuthorizationUrl(
            [
                'scope' => $this->config['scope'],
                'state' => $state,
            ]
        );

        return $url;
    }

    /**
     * @param $accessToken
     * @return array
     */
    public function getUserInfo($accessToken): array
    {
        if (empty($this->config['urlUserInfo'])) {
            return [];
        }

        $factory = $this->oAuth2Provider->getRequestFactory();
        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken->getToken(),
            ],
        ];

        $request = $factory->getRequestWithOptions(
            OAuth2Provider::METHOD_GET,
            $this->config['urlUserInfo'],
            $options
        );

        try {
            $result = $this->oAuth2Provider->getParsedResponse($request);
        } catch (\Exception $e) {
            return [];
        }

        if (!is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * @param array $options
     * @return AccessToken
     */
    public function getAccessToken(array $options = []): AccessToken
    {
        return $this->oAuth2Provider->getAccessToken('authorization_code', $options);
    }

    /**
     * @param $state
     * @return bool
     */
    public function checkState($state): bool
    {
        $result = $state === $this->session->get(static::STATE_KEY);
        $this->session->remove(static::STATE_KEY);
        return $result;
    }
}
