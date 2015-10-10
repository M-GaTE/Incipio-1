<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Tests\DataFixtures\Faker\Provider;

use ApiBundle\DataFixtures\Faker\Provider\UserProvider;
use ApiBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * TODO: mock services instead using a KernelTestCase.
 *
 * @coversDefaultClass ApiBundle\DataFixtures\Faker\Provider\UserProvider
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class UserProviderTest extends KernelTestCase
{
    /**
     * Since tested values are random, they are tested a number of times. This constant is the number of iterations.
     *
     * @var int
     */
    const N = 500;

    /**
     * @var UserProvider
     */
    private $provider;

    /**
     * @var array List of known roles by the application.
     */
    private $roles = [];

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->provider = self::$kernel->getContainer()->get('faker.provider.user');
        $this->roles = self::$kernel->getContainer()->get('api.user.roles')->getRoles();
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $userRoles = $this->prophesize(\ApiBundle\Utils\UserRoles::class);
        new UserProvider($userRoles->reveal());
        $this->assertTrue(true);
    }

    /**
     * @testdox Test the UserProvider::userRole
     *
     * @covers ::userRole
     */
    public function testUserRoles()
    {
        for ($i = 0; $i <= self::N; ++$i) {
            $this->assertTrue(in_array($this->provider->userRole(), $this->roles), 'Expected to generate a known role');
        }
    }

    /**
     * @testdox Test the UserProvider::userTypes
     *
     * @covers ::userTypes
     */
    public function testUserTypes()
    {
        $allowedTypes = User::getAllowedTypes();

        // Test random generation
        for ($i = 0; $i <= self::N; ++$i) {
            $types = $this->provider->userTypes();
            foreach ($types as $type) {
                $this->assertTrue(in_array($type, $allowedTypes), 'Expected to generate a valid type');
            }
        }

        // Test get specified type
        $this->assertEquals([User::TYPE_CONTRACTOR], $this->provider->userTypes('contractor'));
        $this->assertEquals([User::TYPE_MEMBER], $this->provider->userTypes('member'));

        // Test get invalid type
        $this->assertEquals([], $this->provider->userTypes('unknown'));
    }
}
