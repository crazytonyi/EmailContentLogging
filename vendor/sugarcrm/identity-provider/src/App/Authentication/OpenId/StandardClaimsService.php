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

namespace Sugarcrm\IdentityProvider\App\Authentication\OpenId;

use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\STS\Claims;
use Sugarcrm\IdentityProvider\App\Application;

/**
 * OpenID claims converter
 */
class StandardClaimsService
{
    /**
     * user based claims
     * @var array
     */
    protected $userClaimMapping = [
        'status' => 'status',
        'user_type' => 'user_type',
    ];

    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Convert Identity Provider user attributes to OpenID claims.
     * Result omits attributes without a value.
     * @param User $user
     * @param array $requestedScopes
     * @return array
     */
    public function getUserClaims(User $user, array $requestedScopes = []): array
    {
        $user = $user->getLocalUser();

        $mappedData = [
            'preferred_username' => $this->getUsername($user),
            'created_at' => $this->getTimestamp($user->getAttribute('create_time')),
            'updated_at' => $this->getTimestamp($user->getAttribute('modify_time')),
            // We store 'locale' in current application's session, not in User entity
            // Send locale only if the user selected it
            'locale' => $this->app['selectedUserLocale'] ?? null
        ];

        //@TODO remove hot fix
        if (key_exists('locale', $mappedData) && 'it-iT' === $mappedData['locale']) {
            $mappedData['locale'] = 'it-IT';
        }

        foreach ($this->userClaimMapping as $claimName => $userAttributeName) {
            $mappedData[$claimName] = $user->getAttribute($userAttributeName);
        }
        foreach (Claims::OIDC_ATTRIBUTES as $claimName) {
            $mappedData[$claimName] = $user->getOidcAttribute($claimName);
        }
        $mappedData = array_filter($mappedData, function ($value) {
            return !is_null($value);
        });
        $forbiddenClaims = array_reduce(
            array_diff_key(Claims::CLAIMS_BY_SCOPE, array_flip($requestedScopes)),
            function ($carry, $value) {
                $carry = array_merge($carry, $value);
                return $carry;
            },
            []
        );
        return array_diff_key($mappedData, array_flip($forbiddenClaims));
    }

    /**
     * return username
     * @param User $user
     * @return null|string
     */
    protected function getUsername(User $user)
    {
        $username = $user->getUsername();
        return !empty($username) ? $username : $user->getAttribute('identity_value');
    }

    /**
     * convert date string into timestamp
     * @param string $date
     * @return int|null
     */
    protected function getTimestamp($date):? int
    {
        if (empty($date)) {
            return null;
        }
        try {
            $date = new \DateTime($date, new \DateTimeZone('UTC'));
        } catch (\Exception $e) {
            return null;
        }
        return $date->getTimestamp();
    }
}
