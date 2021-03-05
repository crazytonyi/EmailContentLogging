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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Authentication\RememberMe;

use Sugarcrm\IdentityProvider\App\Authentication\RememberMe\Service;
use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\LocalUserProvider;

use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeToken;
use Sugarcrm\IdentityProvider\Authentication\RememberMe\RememberMeTokenInterface;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\User;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Doctrine\DBAL\Connection;
use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\UserProviderBuilder;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Authentication\RememberMe\Service
 */
class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | Connection
     */
    protected $db;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | SessionInterface
     */
    protected $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | SessionInterface
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | Service
     */
    protected $service;

    protected function setUp()
    {
        $this->db = $this->createMock(Connection::class);
        $this->session = new Session(new MockArraySessionStorage());

        $this->userProviderBuilder = $this->createMock(UserProviderBuilder::class);
        $this->provider = $this->createMock(LocalUserProvider::class);
        $this->userProviderBuilder->method('build')
            ->willReturn($this->provider);

        $this->service = $this->getMockBuilder(Service::class)
            ->setConstructorArgs(
                [
                    $this->session,
                    $this->userProviderBuilder,
                ]
            )
            ->setMethods(['none'])
            ->getMock();
    }

    /**
     * Provides data for testStore
     * @return array
     */
    public function storeProvider(): array
    {
        return [
            'oneUser' => [
                'count' => 1,
                'expectedCount' => 1,
            ],
            'tenUsers' => [
                'count' => 10,
                'expectedCount' => 10,
            ],
            'elevenUsers' => [
                'count' => 11,
                'expectedCount' => Service::MAX_IDENTITIES,
            ],
        ];
    }

    /**
     * @param int $count
     * @param int $expectedCount
     *
     * @covers ::store
     * @covers ::list
     * @dataProvider storeProvider
     */
    public function testStore(int $count, int $expectedCount): void
    {
        for ($i = 0; $i < $count; $i++) {
            /** @var RememberMeToken $token */
            $token = new RememberMeToken(
                new UsernamePasswordToken('user_' . $i, '', Providers::LOCAL)
            );
            $token->setAttribute('srn', 'srn:user_'.$i);
            $this->service->store($token);
        }

        $stored = $this->service->list();
        $this->assertCount($expectedCount, $stored);

        $lastLoggedInToken = array_shift($stored);
        $this->assertEquals('user_' . (--$count), $lastLoggedInToken->getUsername());
        $this->assertEquals(
            RememberMeToken::LOGGED_IN | RememberMeToken::ACTIVE,
            $lastLoggedInToken->getAttribute(RememberMeToken::TOKEN_STATUS)
        );

        $checkId = $count;
        /** @var UsernamePasswordToken $token */
        foreach ($stored as $key => $token) {
            $this->assertEquals('user_' . ($checkId - 1), $token->getUsername());
            $this->assertEquals(
                RememberMeToken::LOGGED_IN,
                $token->getAttribute(RememberMeToken::TOKEN_STATUS)
            );
            $checkId--;
        }
    }

    /**
     * @covers ::store
     * @covers ::list
     */
    public function testStoreExistingUser(): void
    {
        $token1 = new UsernamePasswordToken('user_1', '', Providers::LOCAL);
        $token1 = new RememberMeToken($token1);
        $token1->setAttribute('srn', 'srn:user_1');

        $token2 = new UsernamePasswordToken('user_2', '', Providers::LOCAL);
        $token2 = new RememberMeToken($token2);
        $token2->setAttribute('srn', 'srn:user_2');

        $this->service->store($token1);
        $this->service->store($token2);
        $active = $this->service->retrieve();

        $this->assertEquals($token2, $active);

        $this->service->store($token1);
        $active = $this->service->retrieve();

        $this->assertCount(2, $this->service->list());
        $this->assertEquals($token1, $active);
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $token = new RememberMeToken(
            new UsernamePasswordToken('user', '', Providers::LOCAL)
        );
        $this->service->store($token);
        $this->assertCount(1, $this->service->list());
        $this->service->clear();
        $this->assertCount(0, $this->service->list());
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $token1 = new RememberMeToken(
            new UsernamePasswordToken('user_1', '', Providers::LOCAL)
        );
        $token1->setAttribute('srn', 'srn:user_1');

        $token2 = new RememberMeToken(
            new UsernamePasswordToken('user_2', '', Providers::LOCAL)
        );
        $token2->setAttribute('srn', 'srn:user_2');

        $this->service->store($token1);
        $this->service->store($token2);
        $stored = $this->service->list();

        $this->assertCount(2, $stored);

        $this->service->remove($stored[0]);
        $stored = $this->service->list();
        $this->assertCount(1, $stored);
        $this->assertNotEquals($token2, array_shift($stored));
    }

    /**
     * @return array
     */
    public function providerRetrieveWrongToken()
    {
        $u2 = new UsernamePasswordToken('test', null, Providers::LDAP);
        $u2 = new RememberMeToken($u2);
        $u3 = new UsernamePasswordToken('test', null, Providers::LOCAL);
        $u3 = new RememberMeToken($u3);
        $u4 = new UsernamePasswordToken(new User(), null, Providers::LOCAL);
        $u4 = new RememberMeToken($u4);
        return [
            [$u2, $u2],
            [$u3, $u3],
            [$u4, $u4],
        ];
    }

    /**
     * @covers ::retrieve
     * @param TokenInterface $token
     * @param RememberMeToken $expectedToken
     * @dataProvider providerRetrieveWrongToken
     */
    public function testRetrieveWrongToken(TokenInterface $token, RememberMeToken $expectedToken)
    {
        $this->service->store($token);
        $this->assertEquals($expectedToken, $this->service->retrieve());
    }

    /**
     * @covers ::retrieve
     */
    public function testRetrieveInactiveUser()
    {
        $user = new User('test-user-id', '', [
            'id' => 'test-user-id',
            'password_hash' => 'test_password_hash',
        ]);

        $token = new RememberMeToken(
            new UsernamePasswordToken($user, null, Providers::PROVIDER_KEY_LOCAL)
        );
        $token->setAttribute('tenantSrn', 'srn:dev:iam:na:1144464366:tenant');

        $this->service->store($token);

        $this->provider->expects($this->once())
            ->method('loadUserByFieldAndProvider')
            ->with('test-user-id', Providers::LOCAL)
            ->willThrowException(new UsernameNotFoundException());

        $this->assertNull($this->service->retrieve());
    }

    /**
     * @covers ::retrieve
     */
    public function testRetrieve()
    {
        $user = new User('test-user-id', '', [
            'id' => 'test-user-id',
            'password_hash' => 'test_password_hash',
        ]);

        $token = new RememberMeToken(
            new UsernamePasswordToken($user, null, Providers::PROVIDER_KEY_LOCAL)
        );
        $token->setAttribute('tenantSrn', 'srn:dev:iam:na:1144464366:tenant');

        $this->service->store($token);

        $this->assertEquals($token->getUser(), $this->service->retrieve()->getSource()->getUser());
    }

    /**
     * @covers ::retrieveBySrn
     */
    public function testRetrieveBySrn()
    {
        $user = new User('test-user-id', '', [
            'id' => 'test-user-id',
            'password_hash' => 'test_password_hash',
        ]);

        $token = new RememberMeToken(
            new UsernamePasswordToken($user, null, Providers::PROVIDER_KEY_LOCAL)
        );
        $token->setAttribute('srn', 'srn:dev:iam::2000000001:user:1');

        $this->service->store($token);

        // Existing
        $this->assertNotNull($this->service->retrieveBySrn('srn:dev:iam::2000000001:user:1'));
        $this->assertEquals(
            $token->getUser(),
            $this->service->retrieveBySrn('srn:dev:iam::2000000001:user:1')->getSource()->getUser()
        );

        // Missing
        $this->assertNull($this->service->retrieveBySrn('srn:dev:iam::2000000001:user:no_such_user'));
    }

    /**
     * @covers ::__construct
     * @covers ::handleOldTokens
     */
    public function testHandleOldTokensWithOneOldToken(): void
    {
        $tokens = [
            new UsernamePasswordToken('user', 'password', 'local'),
        ];
        $this->session->set(Service::STORAGE_KEY, $tokens);

        $service = new Service(
            $this->session,
            $this->createMock(UserProviderBuilder::class)
        );

        $tokens = $service->list();

        $token = $tokens[0];

        $this->assertInstanceOf(RememberMeTokenInterface::class, $token);
        $this->assertTrue($token->isActive());
    }

    /**
     * @covers ::__construct
     * @covers ::handleOldTokens
     */
    public function testHandleOldTokensWithOneOldTokenAndNewInactiveToken(): void
    {
        $tokens = [
            new UsernamePasswordToken('user', 'password', 'local'),
            new RememberMeToken(new UsernamePasswordToken('user1', 'password1', 'local')),
        ];
        $this->session->set(Service::STORAGE_KEY, $tokens);

        $service = new Service(
            $this->session,
            $this->createMock(UserProviderBuilder::class)
        );

        $tokens = $service->list();

        $this->assertCount(2, $tokens);
        foreach ($tokens as $token) {
            $this->assertInstanceOf(RememberMeTokenInterface::class, $token);
        }

        $token = $tokens[0];
        $this->assertTrue($token->isActive());

        $token = $tokens[1];
        $this->assertFalse($token->isActive());
    }

    /**
     * @covers ::__construct
     * @covers ::handleOldTokens
     */
    public function testHandleOldTokensWithOneOldTokenAndNewActiveToken(): void
    {
        $newToken = new RememberMeToken(
            new UsernamePasswordToken('user1', 'password1', 'local')
        );
        $newToken1 = new RememberMeToken(
            new UsernamePasswordToken('user1', 'password1', 'local')
        );
        $newToken->setLoggedActive();
        $tokens = [
            new UsernamePasswordToken('user', 'password', 'local'),
            $newToken,
            $newToken1,
        ];

        $this->session->set(Service::STORAGE_KEY, $tokens);

        $service = new Service(
            $this->session,
            $this->createMock(UserProviderBuilder::class)
        );

        $tokens = $service->list();

        $this->assertCount(3, $tokens);
        foreach ($tokens as $token) {
            $this->assertInstanceOf(RememberMeTokenInterface::class, $token);
        }

        $token = $tokens[0];
        $this->assertFalse($token->isActive());

        $token = $tokens[1];
        $this->assertTrue($token->isActive());

        $token = $tokens[2];
        $this->assertFalse($token->isActive());
    }

    /**
     * @covers ::__construct
     * @covers ::handleOldTokens
     */
    public function testHandleOldTokensWithNewTokens(): void
    {
        $user1 = new UsernamePasswordToken('user1', 'password1', 'local');
        $newToken1 = new RememberMeToken($user1);
        $newToken1->setAttribute('srn', 'srn:user_1');
        $newToken1->setLoggedActive();

        $user2 = new UsernamePasswordToken('user2', 'password2', 'local');
        $newToken2 = new RememberMeToken($user2);
        $newToken2->setAttribute('srn', 'srn:user_2');

        $this->session->set(Service::STORAGE_KEY, [$newToken1, $newToken2]);

        $service = new Service(
            $this->session,
            $this->createMock(UserProviderBuilder::class)
        );

        $storedTokens = $service->list();

        $token1 = $storedTokens[0];
        $token2 = $storedTokens[1];

        $this->assertEquals($newToken1, $token1);
        $this->assertEquals($newToken2, $token2);
        $this->assertTrue($token1->isActive());
        $this->assertFalse($token2->isActive());
    }
}
