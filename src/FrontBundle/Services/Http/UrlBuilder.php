<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Services\Http;

use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class UrlBuilder
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Expands a URI template and inherit from the base URL if it's relative.
     *
     * @param string      $baseUrl    Base URL to which will be applied the URI.
     * @param string|null $url        URL or an array of the URI template to expand followed by a hash of template
     *                                varnames.
     * @param array       $parameters Route name parameters. If $url parameter passed is not a route name, this
     *                                parameter is ignored.
     *
     * @return string URL
     *
     * @throws RouteNotFoundException              If the named route doesn't exist
     * @throws MissingMandatoryParametersException When some parameters are missing that are mandatory for the route
     * @throws InvalidParameterException           When a parameter value for a placeholder is not correct because it
     *                                             does not match the requirement
     */
    public function buildUrl($baseUrl, $url = null, array $parameters = [])
    {
        if (null === $url || '' === $url) {
            return $baseUrl;
        }

        // Is absolute URL, left unchanged
        if (false !== strpos($url, '://')) {
            return $url;
        }

        // Is URI
        if (false !== strpos($url, '/')) {
            if ('/' === $baseUrl[strlen($baseUrl) - 1]) {
                $baseUrl = substr($baseUrl, 0, strlen($baseUrl) - 1);
            }

            return sprintf('%s%s', $baseUrl, $url);
        }

        // Is a route name
        return $this->buildUrl($baseUrl, $this->router->generate($url, $parameters));
    }
}
