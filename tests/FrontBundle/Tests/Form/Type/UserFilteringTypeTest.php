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

use FrontBundle\Form\Type\UserFilteringType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @coversDefaultClass FrontBundle\Form\Type\UserFilteringType
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class UserFilteringTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = [
            'mandate_id' => '/api/mandates/1',
            'user_type' => '0',
        ];

        // Check that the form compiles
        $type = new UserFilteringType([
            'Mandate 2014/2015' => '/api/mandates/1',
            'Mandate 11 2013' => '/api/mandates/2',
        ]);
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
        $this->assertEquals(count($formData), count($children));
    }
}
