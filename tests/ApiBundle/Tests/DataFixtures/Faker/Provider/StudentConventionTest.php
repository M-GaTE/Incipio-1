<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Tests\DataFixtures\Faker\Provider;

use ApiBundle\DataFixtures\Faker\Provider\StudentConventionProvider;
use ApiBundle\Test\Mock\Faker\GeneratorMock;
use Faker\Factory;

/**
 * @coversDefaultClass ApiBundle\DataFixtures\Faker\Provider\StudentConventionProvider
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class StudentConventionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StudentConventionProvider
     */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->provider = new StudentConventionProvider(new GeneratorMock());
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $fakerGenerator = Factory::create();
        new StudentConventionProvider($fakerGenerator);
        $this->assertTrue(true);
    }

    /**
     * @testdox Test the UserProvider::generateReference
     *
     * @covers ::generateReference
     * @covers ::normalizeString
     * @dataProvider referenceProvider
     *
     * @param           $fullname
     * @param \DateTime $dateOfSignature
     * @param           $expected
     */
    public function testGenerateReference($fullname, \DateTime $dateOfSignature, $expected)
    {
        $this->assertEquals($expected, $this->provider->generateReference($dateOfSignature, $fullname));
    }

    public function referenceProvider()
    {
        return [
            [
                'John Doe',
                new \DateTime('2015-01-31'),
                'JOHDOE20150131',
            ],
            [
                'A Lodoni',
                new \DateTime('2015-01-31'),
                'ALODAB20150131',
            ],
            [
                'Alexander A',
                new \DateTime('2015-01-31'),
                'ALEAAB20150131',
            ],
            [
                'Pi A',
                new \DateTime('2015-01-31'),
                'PIAABC20150131',
            ],
            [
                'Jean-Reneau Baptiste',
                new \DateTime('2015-01-31'),
                'JEABAP20150131',
            ],
            [
                'Dr. Uriah Okuneva',
                new \DateTime('2015-01-31'),
                'DRURIO20150131',
            ],
            [
                '',
                new \DateTime('2015-01-31'),
                'RANNAM20150131',
            ],
            [
                null,
                new \DateTime('2015-01-31'),
                'RANNAM20150131',
            ],
        ];
    }
}
