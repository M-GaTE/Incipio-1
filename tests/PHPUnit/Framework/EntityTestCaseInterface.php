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
 * Tests cases implementing this interface will ensure the basic properties of the entity will be tested.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface EntityTestCaseInterface extends FluentTestCaseInterface
{
    /**
     * @return string Tested entity FQCN
     */
    public function getEntityClassName();

    /**
     * @testdox Test the entity property accessors (getters, setters, hassers, issers).
     *
     * @param array $data
     */
    public function testPropertyAccessors(array $data = []);

    /**
     * Ensures that when the entity is deleted, the relations are properly unset.
     *
     * @coversNothing
     *
     * @param array $data
     */
    public function testDeleteEntity(array $data = []);

    /**
     * @testdox       Test the model validation constraints with valid data.
     *
     * @coversNothing
     *
     * @param array      $data   The data with which the new entity will be populated.
     * @param array|null $groups The validation groups to validate.
     */
    public function testValidationConstraintsWithValidData(array $data, $groups = null);

    /**
     * @testdox Test the model validation constraints with invalid data.
     *
     * @coversNothing
     *
     * @param array      $data   The data with which the new entity will be populated.
     * @param array|null $groups The validation groups to validate.
     */
    public function testValidationConstraintsWithInvalidData(array $data, $groups = null);
}
