<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Tests\Doctrine\DBAL\Type;

use ApiBundle\Doctrine\DBAL\Type\UTCDateTimeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @coversDefaultClass ApiBundle\Doctrine\DBAL\Type\UTCDateTimeType
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class UTCDateTimeTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UTCDateTimeType
     */
    private $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        Type::overrideType('datetime', UTCDateTimeType::class);
        $this->type = Type::getType('datetime');
    }

    /**
     * @covers ::convertToDatabaseValue
     * @dataProvider phpValueProvider
     */
    public function testConvertToDatabaseValue($date)
    {
        $platform = $this->prophesize(AbstractPlatform::class);
        $platform->getDateTimeFormatString()->willReturn('e');
        $actual = $this->type->convertToDatabaseValue($date, $platform->reveal());
        $expected = (null === $date) ? null : 'UTC';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @testbox Convert to PHP value
     *
     * @covers ::convertToPHPValue
     * @dataProvider databaseValueProvider
     */
    public function testConvertToPHPValue($databaseDate)
    {
        $platform = $this->prophesize(AbstractPlatform::class);
        $platform->getDateTimeFormatString()->willReturn('e');
        $actual = $this->type->convertToPHPValue($databaseDate, $platform->reveal());

        if (null === $databaseDate) {
            $this->assertNull($actual);
        } else {
            $this->assertEquals('UTC', $actual->format('e'));
        }
    }

    public function phpValueProvider()
    {
        return [
            [\DateTime::createFromFormat('Y-m-d H:i:s', '2012-02-10 15:10:50', new \DateTimeZone('UTC'))],
            [\DateTime::createFromFormat('Y-m-d H:i:s', '2012-02-10 15:10:50', new \DateTimeZone('Europe/Paris'))],
            [\DateTime::createFromFormat('Y-m-d H:i:s', '2012-02-10 15:10:50', new \DateTimeZone('Europe/Zurich'))],
            [null],
        ];
    }

    public function databaseValueProvider()
    {
        return [
            [\DateTime::createFromFormat('Y-m-d H:i:s', '2012-02-10 15:10:50', new \DateTimeZone('UTC'))],
            [\DateTime::createFromFormat('Y-m-d H:i:s', '2012-02-10 15:10:50', new \DateTimeZone('Europe/Paris'))],
            [\DateTime::createFromFormat('Y-m-d H:i:s', '2012-02-10 15:10:50', new \DateTimeZone('Europe/Zurich'))],
            [null],
        ];
    }
}
