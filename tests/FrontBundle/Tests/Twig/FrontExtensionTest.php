<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Tests\Twig;

use FrontBundle\Twig\FrontExtension;

/**
 * @coversDefaultClass FrontBundle\Twig\FrontExtension
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class FrontExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FrontExtension
     */
    protected $extension;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->extension = new FrontExtension();
    }

    /**
     * This test is more to ensure that the name is not accidentally modified rather than testing the function itself.
     */
    public function testName()
    {
        $this->assertEquals('front_extension', $this->extension->getName());
    }

    /**
     * @testdox Test if the filter is properly returned.
     *
     * @covers ::getFilters
     */
    public function testGetFilters()
    {
        $filters = $this->extension->getFilters();

        $this->assertGreaterThanOrEqual(1, $filters);
    }

    /**
     * @testdox  Test FrontExtension::uriIdFilter().
     *
     * @covers       ::uriIdFilter
     * @dataProvider uriIdProvider
     *
     * @param string $uri      Input value.
     * @param string $expected Expected output value.
     */
    public function testUriIdFilter($uri, $expected)
    {
        $actual = $this->extension->uriIdFilter($uri);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox  Test FrontExtension::userTopRoleFilter().
     *
     * @covers       ::userTopRoleFilter
     * @dataProvider rolesProvider
     *
     * @param array  $roles
     * @param string $expected
     */
    public function testRoleFilter($roles, $expected)
    {
        $actual = $this->extension->userTopRoleFilter($roles);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array List of "uri ID" with their true ID.
     */
    public function uriIdProvider()
    {
        return [
            ['/api/users/101', '101'],
            ['/mandates/102', '102'],
            ['/32', '32'],
            ['/api/users/admin', 'admin'],
            ['/api/users/this-is-a-slug', 'this-is-a-slug'],
            ['/api/users/ii6VpD72', 'ii6VpD72'],
            ['/api/users/', ''],
        ];
    }

    /**
     * @return array List of Symfony role and their expected result.
     */
    public function rolesProvider()
    {
        return [
            [['ROLE_USER'], 'user'],
            [['ROLE_ADMIN'], 'admin'],
            [['ROLE_SUPER_ADMIN'], 'root'],
            [['ROLE_UNKNOWN'], ''],
            [['ROLE_SUPER_UNKNOWN'], ''],

            [['ROLE_USER', 'ROLE_ADMIN'], 'admin'],
            [['ROLE_ADMIN', 'ROLE_USER'], 'admin'],

            [['ROLE_USER', 'ROLE_SUPER_ADMIN'], 'root'],
            [['ROLE_SUPER_ADMIN', 'ROLE_USER'], 'root'],

            [['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], 'root'],
            [['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'], 'root'],

            [['ROLE_USER', 'ROLE_UNKNOWN'], 'user'],
            [['ROLE_UNKNOWN', 'ROLE_USER'], 'user'],

            [['ROLE_SUPER_UNKNOWN', 'ROLE_UNKNOWN'], ''],
            [['ROLE_UNKNOWN', 'ROLE_SUPER_UNKNOWN'], ''],
        ];
    }
}
