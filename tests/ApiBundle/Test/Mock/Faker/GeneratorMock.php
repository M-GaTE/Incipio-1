<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Test\Mock\Faker;

use Faker\Generator;

/**
 * Class GeneratorMock: mocking class for {@see Faker\Generator}. This mock is build this way as the original class
 * uses PHP magic functions to call the formatters, which could not be done via PHPUnit MockBuilder or PHPSpec Prophecy.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class GeneratorMock extends Generator
{
    /**
     * @return string
     */
    public function name()
    {
        return 'Random name';
    }
}
