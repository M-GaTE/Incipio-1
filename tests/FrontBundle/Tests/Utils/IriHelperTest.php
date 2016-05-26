<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Tests\Utils;

use FrontBundle\Utils\IriHelper;

/**
 * @coversDefaultClass FrontBundle\Utils\IriHelper
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class IriHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox Test RoleHierarchyHelper::extractId().
     *
     * @covers       ::extractId
     * @dataProvider uriProvider
     *
     * @param string      $uri
     * @param string|null $expected
     */
    public function testExtractId($uri, $expected)
    {
        $this->assertEquals($expected, IriHelper::extractId($uri));
    }

    /**
     * @return array List of roles.
     */
    public function uriProvider()
    {
        return [
            ['/api/users/23', '23'],
            ['23', '23'],
            ['api/users/23', 'api/users/23'],
            ['/api/users/]QW:M7c4Upzu>4yeTNC;7<2#L8=?i2b59', ']QW:M7c4Upzu>4yeTNC;7<2#L8=?i2b59'],
            ['/api/users/23/', ''],
        ];
    }
}
