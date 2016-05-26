<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Tests\Form\DataTransformer;

use FrontBundle\Form\DataTransformer\StudentConventionTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @coversDefaultClass FrontBundle\Form\DataTransformer\StudentConventionTransformer
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class StudentConventionTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $transformer = new StudentConventionTransformer();
        $this->assertInstanceOf(DataTransformerInterface::class, $transformer);
    }

    /**
     * @covers ::transform
     * @covers ::reverseTransform
     * @dataProvider transformProvider
     *
     * @param array|null $transformData Data to be transformed
     * @param array|null $expected      Expected transformed data
     */
    public function testTransformer($transformData, $expected)
    {
        $transformer = new StudentConventionTransformer();

        $transformedData = $transformer->transform($transformData);
        $this->assertEquals($expected, $transformedData);

        // transform operation must be bijective
        $reversedTransformData = $transformer->reverseTransform($transformedData);
        $this->assertEquals($transformData, $reversedTransformData);
    }

    public function transformProvider()
    {
        return [
            [
                null,
                null,
            ],
            [
                [],
                [],
            ],
            [
                [
                    '@id' => '/api/student_conventions/REFADR20140104',
                    'dateOfSignature' => '2008-02-02T05:36:42+00:00',
                ],
                [
                    '@id' => 'REFADR20140104',
                    'dateOfSignature' => new \DateTime('2008-02-02T05:36:42+00:00'),
                ],
            ],
            [
                [
                    '@id' => null,
                    'dateOfSignature' => null,
                ],
                [
                    '@id' => null,
                    'dateOfSignature' => null,
                ],
            ],
            [
                [
                    '@id' => '',
                    'dateOfSignature' => '',
                ],
                [
                    '@id' => null,
                    'dateOfSignature' => null,
                ],
            ],
        ];
    }
}
