<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Tests\Form\Type;

use FrontBundle\Form\Type\StudentConventionType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @coversDefaultClass FrontBundle\Form\Type\StudentConventionType
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class StudentConventionTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        //TODO
        $formData = [];

        // Check that the form compiles
        $type = new StudentConventionType();
        $form = $this->factory->create($type);

        // Submit the data to the form directly
        $form->submit($formData);

        // Check that no error is thrown
        $this->assertTrue($form->isSynchronized());

        // Check the creation of the form view
        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
