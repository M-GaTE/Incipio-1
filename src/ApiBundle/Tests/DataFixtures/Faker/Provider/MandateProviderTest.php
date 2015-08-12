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

use ApiBundle\DataFixtures\Faker\Provider\MandateProvider;

/**
 * @coversDefaultClass ApiBundle\DataFixtures\Faker\Provider\MandateProvider
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class MandateProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Since tested values are random, they are tested a number of times. This constant is the number of iterations.
     *
     * @var int
     */
    const N = 500;

    /**
     * @var MandateProvider
     */
    private $provider;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->provider = new MandateProvider(\Faker\Factory::create());
    }

    /**
     * @testdox Test MandateProvider::startMandateDateTime() with invalid input.
     * Expect to get a datetime with current year.
     *
     * @covers       ::startMandateDateTime
     * @dataProvider invalidStartMandateDateTimeInputProvider
     *
     * @param string|int $data Invalid input.
     */
    public function testStartMandateDateTime_withInvalidData($data)
    {
        $date = new \DateTime();
        $currentYear = $date->format('Y');

        $date = $this->provider->startMandateDateTime($data);
        $this->assertEquals($currentYear, $date->format('Y'), 'Expected date of current year.');
    }

    /**
     * @testdox Test MandateProvider::startMandateDateTime() with valid input.
     * Expect to get a datetime with the specified year.
     *
     * @covers ::startMandateDateTime
     */
    public function testStartMandateDateTime_withValidData()
    {
        for ($i = 0; $i <= self::N; ++$i) {
            $date = $this->provider->startMandateDateTime(2014);
            $this->assertEquals('2014', $date->format('Y'), 'Expected date of the same year.');

            $date = $this->provider->startMandateDateTime('2014');
            $this->assertEquals('2014', $date->format('Y'), 'Expected date of the same year.');
        }
    }

    /**
     * @testdox Test MandateProvider::endMandateDateTime() with valid input.
     * Expect to get a datetime with the specified year.
     *
     * Note: not tested with invalid input since it has been designed to be used with the result of
     * MandateProvider::startMandateDateTime() as an input!
     *
     * @covers ::endMandateDateTime
     */
    public function testEndMandateDateTime()
    {
        $input = new \DateTime('2014-04-15');

        $expectedStartDate = new \DateTime();
        $expectedStartDate->setDate(2014, 7, 01);

        $expectedEndDate = new \DateTime();
        $expectedEndDate->setDate(2016, 4, 01);

        for ($i = 0; $i <= self::N; ++$i) {
            $date = $this->provider->endMandateDateTime($input);
            $this->assertGreaterThan($expectedStartDate->getTimestamp(), $date->getTimestamp());
            $this->assertLessThan($expectedEndDate->getTimestamp(), $date->getTimestamp());
        }
    }

    /**
     * @return array Invalid inputs for MandateProvider::startMandateDateTime().
     */
    public function invalidStartMandateDateTimeInputProvider()
    {
        return [
            ['now'],
            ['-10 year'],
            ['invalid string...'],
            [10],
            ['10'],
            [999],
            ['999'],
            [10000],
            ['10000'],
        ];
    }
}
