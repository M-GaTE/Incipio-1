<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

/**
 * Tests cases implementing this interface will ensure the tested elements will implements the fluent interface.
 *
 * @link   http://en.wikipedia.org/wiki/Fluent_interface
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface FluentTestCaseInterface
{
    /**
     * @coversNothing
     * @testdox Test if the class properly implements the fluent interface.
     *
     * @param array $data Set of data to hydrate the entity with.
     */
    public function testFluentImplementation(array $data = []);
}
