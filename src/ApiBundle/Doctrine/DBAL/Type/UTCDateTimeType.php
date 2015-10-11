<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Doctrine\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * Class UTCDateTimeType: used to set the timezone of any date to UTC.
 *
 * @link https://github.com/Atlantic18/DoctrineExtensions/blob/master/doc/timestampable.md#creating-a-utc-datetime-type-that-stores-your-datetimes-in-utc
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class UTCDateTimeType extends DateTimeType
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($phpValue, AbstractPlatform $platform)
    {
        if ($phpValue instanceof \DateTime) {
            $phpValue->setTimeZone(new \DateTimeZone('UTC'));
        }

        return parent::convertToDatabaseValue($phpValue, $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($databaseValue, AbstractPlatform $platform)
    {
        if (null === $databaseValue || $databaseValue instanceof \DateTime) {
            return $databaseValue;
        }

        // The changed part is the following bloc where we put the DateTimeZone as the third argument rather than
        // relying on the local timezone
        $phpValue = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $databaseValue,
            new \DateTimeZone('UTC')
        );

        if (false === $phpValue) {
            $phpValue = date_create($databaseValue);
        }

        if (false === $phpValue) {
            throw ConversionException::conversionFailedFormat(
                $databaseValue,
                $this->getName(),
                $platform->getDateTimeFormatString()
            );
        }

        return $phpValue;
    }
}
