<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Incipio\Tests\Behat\Tests;

use Incipio\Tests\Behat\Utils\Utils;

/**
 * @coversDefaultClass Incipio\Tests\Behat\Utils\Utils
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::buildTree
     * @dataProvider treeProvider
     *
     * @param string $separator
     * @param string $string
     * @param mixed  $value
     * @param array  $tree
     * @param array  $expected
     */
    public function testBuildTree($separator, $string, $value, array $tree, array $expected)
    {
        // TODO: make this works
//        $actual = Utils::buildTree($separator, $string, $value, $tree);
//        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::recursiveExplode
     * @dataProvider stringProvider
     *
     * @param string $separator
     * @param string $string
     * @param mixed  $value
     * @param array  $expected
     */
    public function testRecursiveExplode($separator, $string, $value, array $expected)
    {
        // TODO: make this works
//        $actual = Utils::recursiveExplode($separator, $string, $value);
//        $this->assertEquals($expected, $actual);
    }

    public function treeProvider()
    {
        return [
            [
                '_',
                'a_b',
                'value',
                [],
                [
                    'a' => [
                        'b' => 'value',
                    ],
                ],
            ],
            [
                '_',
                'a_c',
                'value',
                [
                    'a' => [
                        'b' => 'bValue',
                    ],
                ],
                [
                    'a' => [
                        'b' => 'bValue',
                        'c' => 'value',
                    ],
                ],
            ],
            [
                '_',
                'a_b_c',
                'value',
                [
                    'a' => [
                        'b' => 'bValue',
                    ],
                ],
                [
                    'a' => [
                        'b' => [
                            0 => 'bValue',
                            'c' => 'value',
                        ],
                    ],
                ],
            ],
            [
                '_',
                'a_b_c',
                'value',
                [
                    'a' => [
                        'b' => [
                            'd' => 'dValue',
                        ],
                    ],
                ],
                [
                    'a' => [
                        'b' => [
                            'd' => 'dValue',
                            'c' => 'value',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function stringProvider()
    {
        return [
            [
                '_',
                'a_b',
                'value',
                [
                    'a' => [
                        'b' => 'value',
                    ],
                ],
            ],
            [
                '_',
                'a_b_c',
                'value',
                [
                    'a' => [
                        'b' => [
                            'c' => 'value',
                        ],
                    ],
                ],
            ],
        ];
    }
}
