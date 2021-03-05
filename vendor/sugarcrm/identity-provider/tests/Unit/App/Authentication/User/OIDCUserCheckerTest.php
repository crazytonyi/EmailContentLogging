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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Authentication\User;

use Sugarcrm\IdentityProvider\App\Authentication\User\OIDCUserChecker;
use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\LocalUserProvider;

use Sugarcrm\IdentityProvider\Authentication\Exception\InvalidIdentifier\EmptyFieldException;
use Sugarcrm\IdentityProvider\Authentication\Exception\InvalidIdentifier\EmptyIdentifierException;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;
use Sugarcrm\IdentityProvider\Authentication\User;

use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Authentication\User\OIDCUserChecker
 */
class OIDCUserCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var OIDCUserChecker
     */
    protected $userChecker;

    /**
     * @var LocalUserProvider
     */
    protected $localUserProvider;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->user = $this->createMock(User::class);
        $this->user->method('isCredentialsNonExpired')->willReturn(true);
        $this->localUserProvider = $this->getMockBuilder(LocalUserProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Data provider for testValidIdentifier
     * @return array
     * @see testValidIdentifier
     */
    public function validIdentifierProvider(): array
    {
        return [
            'emptyField' => [
                'expectedException' => EmptyFieldException::class,
                'field' => '',
                'value' => '',
            ],
            'emptyValue' => [
                'expectedException' => EmptyIdentifierException::class,
                'field' => 'someField',
                'value' => '',
            ],
        ];
    }

    /**
     * @param string $expectedException
     * @param string $field
     * @param string $value
     *
     * @covers ::checkPostAuth
     * @dataProvider validIdentifierProvider
     */
    public function testValidIdentifier(string $expectedException, string $field, string $value): void
    {
        $this->user->method('getAttribute')->willReturnMap([
            ['identityField', $field],
            ['identityValue', $value],
        ]);

        $this->expectException($expectedException);

        $userChecker = new OIDCUserChecker($this->localUserProvider, []);
        $userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthIfProvisionOffAndUserDoesNotExist(): void
    {
        $this->expectException(UsernameNotFoundException::class);
        $this->expectExceptionMessage('User not found');

        $this->user->method('getAttribute')->willReturnMap([
            ['identityField', 'sub'],
            ['identityValue', 'userIdentity'],
        ]);
        $config = ['provisionUser' => false];

        $this->localUserProvider->expects($this->at(0))
            ->method('loadUserByFieldAndProvider')
            ->with('userIdentity', Providers::OIDC)
            ->willThrowException(new UsernameNotFoundException('User not found'));
        $this->localUserProvider->expects($this->at(1))
            ->method('loadUserByFieldAndProvider')
            ->with('userIdentity', Providers::LOCAL)
            ->willThrowException(new UsernameNotFoundException('User not found'));

        $this->localUserProvider->expects($this->never())->method('createUser');

        $userChecker = new OIDCUserChecker($this->localUserProvider, $config);
        $userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthIfProvisionOnAndUserDisabled(): void
    {
        $this->expectException(DisabledException::class);
        $this->expectExceptionMessage('User account is disabled');

        $this->user->method('getAttribute')->willReturnMap([
            ['identityField', 'sub'],
            ['identityValue', 'userIdentity'],
        ]);
        $config = ['provisionUser' => true];

        $this->localUserProvider->expects($this->at(0))
            ->method('loadUserByFieldAndProvider')
            ->with('userIdentity', Providers::OIDC)
            ->willThrowException(new UsernameNotFoundException('User not found'));
        $this->localUserProvider->expects($this->at(1))
            ->method('loadUserByFieldAndProvider')
            ->with('userIdentity', Providers::LOCAL)
            ->willThrowException(new UsernameNotFoundException('User not found'));

        $this->localUserProvider->expects($this->once())
            ->method('isDeactivatedUserExist')
            ->with('userIdentity', Providers::OIDC)
            ->willReturn(true);

        $this->localUserProvider->expects($this->never())->method('createUser');

        $userChecker = new OIDCUserChecker($this->localUserProvider, $config);
        $userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthIfProvisionOn(): void
    {
        $this->user->method('getAttribute')->willReturnMap([
            ['identityField', 'sub'],
            ['identityValue', 'userIdentity'],
            ['attributes', ['a' => 'b']]
        ]);
        $config = ['provisionUser' => true];

        $this->localUserProvider->expects($this->at(0))
            ->method('loadUserByFieldAndProvider')
            ->with('userIdentity', Providers::OIDC)
            ->willThrowException(new UsernameNotFoundException('User not found'));
        $this->localUserProvider->expects($this->at(1))
            ->method('loadUserByFieldAndProvider')
            ->with('userIdentity', Providers::LOCAL)
            ->willThrowException(new UsernameNotFoundException('User not found'));

        $this->localUserProvider->expects($this->once())
            ->method('isDeactivatedUserExist')
            ->with('userIdentity', Providers::OIDC)
            ->willReturn(false);

        $localUser = $this->createMock(User::class);
        $this->localUserProvider->expects($this->once())
            ->method('createUser')
            ->with('userIdentity', Providers::OIDC, ['a' => 'b'])
            ->willReturn($localUser);

        $this->user->expects($this->once())->method('setLocalUser')->with($localUser);

        $userChecker = new OIDCUserChecker($this->localUserProvider, $config);
        $userChecker->checkPostAuth($this->user);
    }

    /**
     * @covers ::checkPostAuth
     */
    public function testCheckPostAuthIfProvisionOnAndLocalUserExists(): void
    {
        $this->user->method('getAttribute')->willReturnMap([
            ['identityField', 'sub'],
            ['identityValue', 'userIdentity'],
            ['attributes', ['a' => 'b']]
        ]);
        $localUser = $this->createMock(User::class);
        $localUser->method('getAttribute')->willReturnMap([
            ['id', 'id'],
        ]);
        $config = ['provisionUser' => true];

        $this->localUserProvider->expects($this->at(0))
            ->method('loadUserByFieldAndProvider')
            ->with('userIdentity', Providers::OIDC)
            ->willThrowException(new UsernameNotFoundException('User not found'));
        $this->localUserProvider->expects($this->at(1))
            ->method('loadUserByFieldAndProvider')
            ->with('userIdentity', Providers::LOCAL)
            ->willReturn($localUser);

        $this->localUserProvider->expects($this->once())
            ->method('linkUser')
            ->with('id', Providers::OIDC, 'userIdentity');

        $this->localUserProvider->expects($this->never())->method('createUser');

        $this->user->expects($this->once())->method('setLocalUser')->with($localUser);
        
        $userChecker = new OIDCUserChecker($this->localUserProvider, $config);
        $userChecker->checkPostAuth($this->user);
    }
}
