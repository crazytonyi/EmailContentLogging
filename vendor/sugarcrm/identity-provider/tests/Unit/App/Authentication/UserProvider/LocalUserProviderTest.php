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

namespace Sugarcrm\IdentityProvider\Tests\Unit\App\Authentication\UserProvider;

use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\LocalUserProvider;
use Sugarcrm\IdentityProvider\App\Repository\UserRepository;

use Sugarcrm\IdentityProvider\Authentication\Audit;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\IdentityProvider\Authentication\Provider\Providers;

use Doctrine\DBAL\Connection;

/**
 * Class LocalUserProviderTest.
 * @coversDefaultClass \Sugarcrm\IdentityProvider\App\Authentication\UserProvider\LocalUserProvider
 */
class LocalUserProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $testUserName = 'user1';

    /**
     * @var string
     */
    protected $testPassword = 'passwordnohash';

    public function testLoadUserByUsernameUserExists()
    {
        $userProvider = $this->getUserProvider();
        $user = $userProvider->loadUserByUsername($this->testUserName);
        $this->assertEquals($this->testUserName, $user->getUsername());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @expectedExceptionMessage User not found
     */
    public function testLoadUserByUsernameUserDoesntExist()
    {
        $userProvider = $this->getUserProvider(null);
        $userProvider->loadUserByUsername('unknown_user_name');
    }

    public function testLoadUserByFieldAndProviderExists()
    {
        $userProvider = $this->getUserProvider();
        $user = $userProvider->loadUserByFieldAndProvider($this->testUserName, Providers::LDAP);
        $this->assertEquals($this->testUserName, $user->getUsername());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @expectedExceptionMessage User not found
     */
    public function testLoadUserByFieldAndProviderUserDoesNotExist()
    {
        $userProvider = $this->getUserProvider(null);
        $userProvider->loadUserByFieldAndProvider('unknown_user_name', Providers::SAML);
    }

    public function testRefreshUser()
    {
        $userProvider = $this->getUserProvider();
        $user = new User($this->testUserName, $this->testPassword . 'suffix');
        $user = $userProvider->refreshUser($user);
        $this->assertEquals($this->testPassword, $user->getPassword());
    }

    /**
     * @param $actual
     * @param $expected
     *
     * @dataProvider createUserProvider
     */
    public function testCreateUser($actual, $expected)
    {
        $db = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userProvider = $this->getMockBuilder(LocalUserProvider::class)
            ->setConstructorArgs([
                $db,
                $actual['tenant'],
                'srn:cloud:iam:eu:1000000001:app:idp:123',
                $this->createMock(Audit::class),
                $this->createMock(UserRepository::class),
            ])
            ->setMethods(['getProviderId'])
            ->getMock();

        $db->method('transactional')
            ->will($this->returnCallback(function (callable $callback) use ($db) {
                $callback($db);
            }));

        $userProvider->method('getProviderId')->willReturn(2);

        $db->expects($this->exactly(2))
            ->method('insert')
            ->withConsecutive(
                ['users',
                    $this->logicalAnd(
                        $this->isType('array'),
                        $this->arrayHasKey('attributes'),
                        $this->contains(json_encode($expected['attributes']), false, true),
                        $this->arrayHasKey('custom_attributes'),
                        $this->contains(json_encode($expected['custom_attributes']), false, true),
                        $this->arrayHasKey('status'),
                        $this->contains('0', false, true),
                        $this->arrayHasKey('tenant_id'),
                        $this->contains($expected['tenant'], false, true),
                        $this->arrayHasKey('id'),
                        $this->arrayHasKey('create_time'),
                        $this->arrayHasKey('modify_time'),
                        $this->arrayHasKey('created_by'),
                        $this->arrayHasKey('modified_by')
                    )],
                ['user_providers',
                    $this->logicalAnd(
                        $this->isType('array'),
                        $this->arrayHasKey('tenant_id'),
                        $this->contains($expected['tenant'], false, true),
                        $this->arrayHasKey('identity_value'),
                        $this->contains($expected['identity_value'], false, true),
                        $this->arrayHasKey('provider_code'),
                        $this->contains($expected['provider'], false, true),
                        $this->arrayHasKey('user_id')
                    )]
            );

        /**
         * @var $user User
         */
        $user = $userProvider->createUser($actual['identity_value'], $actual['provider'], $actual['attributes']);

        $this->assertEquals($expected['attributes'], $user->getAttribute('attributes'));
        $this->assertEquals($expected['custom_attributes'], $user->getAttribute('custom_attributes'));
    }

    /**
     * @return array
     */
    public function createUserProvider()
    {
        return [
            [
                [
                    'identity_value' => 'john@ex.com',
                    'provider' => Providers::LDAP,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [
                        'given_name' => 'John',
                        'family_name' => 'Smith',
                        'non-oidc' => 'some-value',
                    ]
                ],
                [
                    'identity_value' => 'john@ex.com',
                    'provider' => Providers::LDAP,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [
                        'family_name' => 'Smith',
                        'email' => 'john@ex.com',
                        'given_name' => 'John',
                    ],
                    'custom_attributes' => [
                        'non-oidc' => 'some-value',
                    ]
                ],
            ],
            [
                [
                    'identity_value' => 'max@ex.com',
                    'provider' => Providers::SAML,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [],
                ],
                [
                    'identity_value' => 'max@ex.com',
                    'provider' => Providers::SAML,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [
                        'family_name' => 'max@ex.com',
                        'email' => 'max@ex.com',
                    ],
                    'custom_attributes' => [],
                ],
            ],
            [
                [
                    'identity_value' => 'max@ex',
                    'provider' => Providers::SAML,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [],
                ],
                [
                    'identity_value' => 'max@ex',
                    'provider' => Providers::SAML,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [
                        'family_name' => 'max_at_ex@some-tenant-id.com',
                        'email' => 'max_at_ex@some-tenant-id.com',
                    ],
                    'custom_attributes' => [],
                ],
            ],
            [
                [
                    'identity_value' => 'max',
                    'provider' => Providers::SAML,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [],
                ],
                [
                    'identity_value' => 'max',
                    'provider' => Providers::SAML,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [
                        'family_name' => 'max@some-tenant-id.com',
                        'email' => 'max@some-tenant-id.com',
                    ],
                    'custom_attributes' => [],
                ],
            ],
            [
                [
                    'identity_value' => 'max@ex',
                    'provider' => Providers::LDAP,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [
                        'family_name' => 'Smith',
                    ],
                ],
                [
                    'identity_value' => 'max@ex',
                    'provider' => Providers::LDAP,
                    'tenant' => 'some-tenant-id',
                    'attributes' => [
                        'family_name' => 'Smith',
                        'email' => 'max_at_ex@some-tenant-id.com',
                    ],
                    'custom_attributes' => [],
                ],
            ],
        ];
    }

    /**
     * @param array|null $data Creates UserProvider object which returns predefined data
     *                         that can be overwritten by $data param.
     *                         UserProvider will return null if $data is not array.
     * @return LocalUserProvider
     */
    protected function getUserProvider($data = [])
    {
        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserData'])
            ->getMock();

        if (is_array($data)) {
            $rowData = array_merge([
                'id' => '12345678-9012-3456-7890-123456789012',
                'identity_value' => $this->testUserName,
                'password_hash' => $this->testPassword,
                'status' => User::STATUS_ACTIVE,
                'create_time' => '',
                'modify_time' => '',
            ], $data);
        } else {
            $rowData = null;
        }

        $userRepository->method('getUserData')
            ->willReturn($rowData);

        $userProvider = $this->getMockBuilder(LocalUserProvider::class)
            ->setConstructorArgs([
                $this->createMock(Connection::class),
                'srn:cloud:iam:eu:1000000001:tenant',
                'srn:cloud:iam:eu:1000000001:app:idp:123',
                $this->createMock(Audit::class),
                $userRepository,
            ])
            ->enableOriginalConstructor()
            ->setMethods(['none'])
            ->getMock();

        return $userProvider;
    }

    /**
     * @covers ::linkUser
     */
    public function testLinkUser(): void
    {
        $tenantId = 'some-tenant-id';
        $identityValue = 'john@ex.com';
        $provider = Providers::LDAP;
        $userId = 'some-user-id';

        $db = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db->expects($this->once())
            ->method('insert')
            ->with(
                'user_providers',
                $this->equalTo([
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'provider_code' => $provider,
                    'identity_value' => $identityValue,
                ])
            );

        $userProvider = new LocalUserProvider(
            $db,
            $tenantId,
            'srn:cloud:iam:eu:1000000001:app:idp:123',
            $this->createMock(Audit::class),
            $this->createMock(UserRepository::class)
        );
        $userProvider->linkUser($userId, $provider, $identityValue);
    }

    /**
     * @see testUpdateUserAttributes
     * @return array
     */
    public function updateUserAttributesProvider(): array
    {
        $userId = 'some-user-id';
        $tenantId = 'some-tenant-id';
        $modifyDate = '2019-06-28 12:46:48';
        $applicationSRN = 'some-application-srn';
        $attributes = [
            'given_name' => 'given_name.value',
            'family_name' => 'family_name.value',
            'middle_name' => 'middle_name.value',
            'nickname' => 'nickname.value',
            'address' => [
                'street_address' => 'address.street_address.value',
                'locality' => 'address.locality.value',
                'region' => 'address.region.value',
            ],
            'email' => 'email.value',
            'phone_number' => 'phone_number.value',
        ];
        $customAttributes = [
            'given_name.custom' => 'given_name.custom.value',
            'family_name.custom' => 'family_name.custom.value',
        ];
        $expectsIdentifier = [
            'id' => $userId,
            'tenant_id' => $tenantId,
        ];

        return [
            'default' => [
                'in' => [
                    'userId' => $userId,
                    'tenantId' => $tenantId,
                    'data' => array_merge($attributes, $customAttributes),
                ],
                'expects' => [
                    'DB_data' => [
                        'modify_time' => $modifyDate,
                        'attributes' => json_encode($attributes),
                        'custom_attributes' => json_encode($customAttributes),
                        'modified_by' => $applicationSRN,
                    ],
                    'object_data' => [
                        'id' => $userId,
                        'modify_time' => $modifyDate,
                        'attributes' => $attributes,
                        'custom_attributes' => $customAttributes,
                        'modified_by' => $applicationSRN,
                    ],
                    'identifier' => $expectsIdentifier,
                ],
            ],
            'emptyAddressArray' => [
                'in' => [
                    'userId' => $userId,
                    'tenantId' => $tenantId,
                    'data' => array_merge($attributes, $customAttributes, ['address' => []]),
                ],
                'expects' => [
                    'DB_data' => [
                        'modify_time' => $modifyDate,
                        'attributes' => json_encode(array_merge($attributes, ['address' => new \stdClass])),
                        'custom_attributes' => json_encode($customAttributes),
                        'modified_by' => $applicationSRN,
                    ],
                    'object_data' => [
                        'id' => $userId,
                        'modify_time' => $modifyDate,
                        'attributes' => array_merge($attributes, ['address' => []]),
                        'custom_attributes' => $customAttributes,
                        'modified_by' => $applicationSRN,
                    ],
                    'identifier' => $expectsIdentifier,
                ],
            ],
            'emptyAttributes' => [
                'in' => [
                    'userId' => $userId,
                    'tenantId' => $tenantId,
                    'data' => $customAttributes,
                ],
                'expects' => [
                    'DB_data' => [
                        'modify_time' => $modifyDate,
                        'attributes' => '{}',
                        'custom_attributes' => json_encode($customAttributes),
                        'modified_by' => $applicationSRN,
                    ],
                    'object_data' => [
                        'id' => $userId,
                        'modify_time' => $modifyDate,
                        'attributes' => [],
                        'custom_attributes' => $customAttributes,
                        'modified_by' => $applicationSRN,
                    ],
                    'identifier' => $expectsIdentifier,
                ],
            ],
            'emptyCustomAttributes' => [
                'in' => [
                    'userId' => $userId,
                    'tenantId' => $tenantId,
                    'data' => $attributes,
                ],
                'expects' => [
                    'DB_data' => [
                        'modify_time' => $modifyDate,
                        'attributes' => json_encode($attributes),
                        'custom_attributes' => '{}',
                        'modified_by' => $applicationSRN,
                    ],
                    'object_data' => [
                        'id' => $userId,
                        'modify_time' => $modifyDate,
                        'attributes' => $attributes,
                        'custom_attributes' => [],
                        'modified_by' => $applicationSRN,
                    ],
                    'identifier' => $expectsIdentifier,
                ],
            ],
        ];
    }

    /**
     * @dataProvider updateUserAttributesProvider
     * @covers ::updateUserAttributes
     * @param array $in
     * @param array $expects
     */
    public function testUpdateUserAttributes(array $in, array $expects)
    {
        /** @var  \PHPUnit_Framework_MockObject_MockObject|Connection $db */
        $db = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user = new User('max', 'psswd', ['id' => $in['userId']]);

        /** @var  \PHPUnit_Framework_MockObject_MockObject|LocalUserProvider $userProvider */
        $userProvider = $this->getMockBuilder(LocalUserProvider::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(
                [
                    $db,
                    $in['tenantId'],
                    'some-application-srn',
                    $this->createMock(Audit::class),
                    $this->createMock(UserRepository::class),
                ]
            )
            ->setMethods(['getCurrentDate', 'getUserData'])
            ->getMock();

        $userProvider->method('getCurrentDate')->willReturn('2019-06-28 12:46:48');

        $db->expects($this->once())
            ->method('update')
            ->with(
                $this->equalTo('users'),
                $this->equalTo($expects['DB_data']),
                $this->equalTo($expects['identifier'])
            );

        $userProvider->updateUserAttributes($in['data'], $user);
        $this->assertEquals($expects['object_data'], $user->getAttributes(), 'User should have updated attributes');
    }

    /**
     * @covers ::getCurrentDate
     */
    public function testGetCurrentDate()
    {
        $provider = new LocalUserProvider(
            $this->createMock(Connection::class),
            'tid1',
            'srn:cloud:iam:eu:1000000001:app:idp:123',
            $this->createMock(Audit::class),
            $this->createMock(UserRepository::class)
        );
        $this->assertRegExp('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $provider->getCurrentDate());
        $d1 = $provider->getCurrentDate();
        $d2 = $provider->getCurrentDate();
        $this->assertGreaterThanOrEqual(strtotime($d1), strtotime($d2));
    }
}
