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

use FrontBundle\Services\Http\RequestBuilder;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * API client. For now is a Guzzle client which has been extended to allow to pass route names instead of just
 * the URI and easily pass the token.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class ApiClient implements ClientInterface
{
    /**
     * @var GuzzleClientInterface
     */
    private $client;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @param GuzzleClientInterface $client
     * @param RequestBuilder        $requestBuilder
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(GuzzleClientInterface $client, RequestBuilder $requestBuilder)
    {
        if ($client->getConfig('base_url') !== $requestBuilder->getBaseUrl()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected base url of client and request build to match. Got respectively "%s" and "%s" instead.',
                    $client->getConfig('base_url'),
                    $requestBuilder->getBaseUrl()
                )
            );
        }

        $this->client = $client;
        $this->requestBuilder = $requestBuilder;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RouteNotFoundException              If the named route doesn't exist
     * @throws MissingMandatoryParametersException When some parameters are missing that are mandatory for the route
     * @throws InvalidParameterException           When a parameter value for a placeholder is not correct because it
     *                                             does not match the requirement
     */
    public function createRequest($method, $url = null, $token = null, array $options = [])
    {
        return $this->requestBuilder->createRequest($method, $url, $token, $options);
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
}
