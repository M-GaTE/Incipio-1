<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Tests\Controller;

use FrontBundle\Controller\BaseController;

/**
 * @coversDefaultClass FrontBundle\Controller\BaseController
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class BaseControllerTest
{
    /**
     * @testdox Test if properly extract ID from an URI
     *
     * @covers       ::generateUrl
     * @dataProvider dataProvider
     *
     * @param string $input    URI or ID to extract.
     * @param string $expected Expected output value.
     */
    public function testExtractId($input, $expected)
    {
        $parameters = ['id' => $input];

        $actual = BaseController::extractId($parameters)['id'];
        $this->assertEquals($expected,
            $actual,
            sprintf('Unexpected URI generated, got `%s` instead of `%s`.', $actual, $expected)
        );
    }

    /**
     * @return array List of data required for the URL generator.
     */
    public function dataProvider()
    {
        return [
            ['/api/users/101', '101'],
            ['101', '101'],
            ['/api/users/this-is-a-slug', 'this-is-a-slug'],
            ['this-is-a-slug', 'this-is-a-slug'],
            ['/api/users/ii6VpD72', 'ii6VpD72'],
            ['ii6VpD72', 'ii6VpD72'],
        ];
    }
}
