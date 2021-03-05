<?php

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Authentication\ConsentRequest;

use Jose\Object\JWSInterface;
use Sugarcrm\IdentityProvider\App\Authentication\ConsentRequest\ConsentToken;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Authentication\ConsentRequest\ConsentToken
 */
class ConsentTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testFillByConsentRequestData()
    {
        $token = (new ConsentToken())->fillByConsentRequestData([
            'id' => 'test_request_id',
            'requestedScopes' => ['offline', 'openid', 'hydra.*'],
            'clientId' => 'testLocal1',
            'redirectUrl' => 'http://test/?tenant_hint=srn:cloud:idp:eu:2000000001:tenant&login_hint=max',
        ]);
        $this->assertEquals('srn:cloud:idp:eu:2000000001:tenant', $token->getTenantSRN());
        $this->assertEquals('max', $token->getUsername());
        $this->assertEquals(
            'http://test/?tenant_hint=srn:cloud:idp:eu:2000000001:tenant&login_hint=max',
            $token->getRedirectUrl()
        );
        $this->assertEquals('testLocal1', $token->getClientId());
        $this->assertEquals('test_request_id', $token->getRequestId());
        $this->assertEquals(['offline', 'openid', 'hydra.*'], $token->getScopes());
        $token->setTenantSRN('srn:cloud:idp:eu:3000000001:tenant');
        $this->assertEquals('srn:cloud:idp:eu:3000000001:tenant', $token->getTenantSRN());
    }

    public function testFillByConsentRequestNoTenant()
    {
        $token = (new ConsentToken())->fillByConsentRequestData([
            'id' => 'test_request_id',
            'requestedScopes' => ['offline', 'openid', 'hydra.*'],
            'clientId' => 'testLocal1',
            'redirectUrl' => 'http://test/',
        ]);
        $this->assertNull($token->getTenantSRN());
    }

    /**
     * @covers ::getClientUrl
     */
    public function testGetClientUrl(): void
    {
        $token = (new ConsentToken())->fillByConsentRequestData([
            'id' => 'test_request_id',
            'requestedScopes' => ['offline', 'openid', 'hydra.*'],
            'clientId' => 'testLocal1',
            'redirectUrl' => urldecode('http://sts/oauth2/auth?scope=offline%20https%3A%2F%2Fapis.sugarcrm.com%2F' .
                'auth%2Fcrm%20profile%20email%20address%20phone&state=base_c3a7aaaa-f51a-464f-b860-7f6a008718e3' .
                '&tenant_hint=srn%3Adev%3Aiam%3Ana%3A1396472465%3Atenant&response_type=code&approval_prompt=auto' .
                '&redirect_uri=http%3A%2F%2Fsugar.dolbik.localhost%2F93%2F%3Fmodule%3DUsers%26action%3DOAuth2Code' .
                'Exchange&client_id=srn%3Adev%3Aiam%3Ana%3A1396472465%3Aapp%3Acrm%3A613d88a2-9c5f-4d26-928d-' .
                '0efeb1dfaa57&consent=f28e7245-ff9a-450f-b16d-cf6e594d9449&consent_csrf=c167e7d8-9c92-47d5-a1f7-' .
                '192506ca07a8'),
        ]);

        $this->assertEquals('http://sugar.dolbik.localhost/93/', $token->getClientUrl());
    }
}
