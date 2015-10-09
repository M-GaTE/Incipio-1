<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Client;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Query;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * PHP API client. For now is a Guzzle client which has been extended to allow to pass route names instead of just
 * the URI and easily pass the token.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ApiClient implements ApiClientInterface
{
    /**
     * @var string Base URL used. Is guaranteed of not having an trailing slash unlike the client baseUrl (which is
     *             not changeable after instantiation.
     */
    private $baseUrl;

    /**
     * @var GuzzleClientInterface
     */
    private $client;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param GuzzleClientInterface $client Adapted service: guzzle client
     * @param Router                $router Component used to generate URI from route names
     */
    public function __construct(GuzzleClientInterface $client, Router $router)
    {
        $this->client = $client;

        // Check for base URL to remove trailing slash if present
        $baseUrl = $this->client->getBaseUrl();
        $lastCharacter = strlen($baseUrl) - 1;
        if ('/' === $baseUrl[$lastCharacter]) {
            $baseUrl = substr($baseUrl, 0, $lastCharacter);
        }
        $this->baseUrl = $baseUrl;

        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest($method, $url = null, $token = null, array $options = [])
    {
        // Extract parameters
        $parameters = [];
        if (isset($options['parameters'])) {
            $parameters = $options['parameters'];
            unset($options['parameters']);
        }

        // Add authorization token
        if (null !== $token) {
            $options['headers']['authorization'] = sprintf('Bearer %s', $token);
        }

        // If Query is a string cast it
        if (isset($options['query']) && is_string($options['query'])) {
            $options['query'] = Query::fromString($options['query']);
        }

        return $this->client->createRequest($method, $this->buildUrl($url, $parameters), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function request($method, $url = null, $token = null, $options = [])
    {
        return $this->client->send($this->createRequest($method, $url, $token, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request)
    {
        return $this->client->send($request);
    }

    /**
     * Expand a URI template and inherit from the base URL if it's relative.
     *
     * @param string|null $url        URL or an array of the URI template to expand
     *                                followed by a hash of template varnames.
     * @param array       $parameters route name parameters. If $url parameter passed is not a route name, this parameter
     *                                is ignored.
     *
     * @return string URL
     */
    private function buildUrl($url = null, array $parameters = [])
    {
        if (null === $url || '' === $url) {
            return $this->baseUrl;
        }

        // Is absolute URL, left unchanged
        if (false !== strpos($url, '://')) {
            return $url;
        }

        // Is URI
        if (false !== strpos($url, '/')) {
            return sprintf('%s%s', $this->baseUrl, $url);
        }

        // Is a route name
        return $this->buildUrl($this->router->generate($url, $parameters));
    }
}
