<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Client\Exception;

use GuzzleHttp\Exception\GuzzleException;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface ClientException extends GuzzleException
{
}
