<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\DataFixtures\Faker\Provider;

/**
 * Extends {@see \Faker\Provider\DateTime}. As all method are static does not literally extend the class to avoid
 * useless overhead.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class DateTimeProvider
{
    /**
     * Parses a string into a new DateTime object according to the specified format.
     *
     * @param string $format Format accepted by date().
     * @param string $time   String representing the time.
     *
     * @return \DateTime
     *
     * @link http://php.net/manual/en/datetime.createfromformat.php
     */
    public static function dateTimeFromFormat($format, $time)
    {
        return \DateTime::createFromFormat($format, $time);
    }
}
