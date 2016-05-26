<?php

namespace FrontBundle\Services\Http;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Class RequestBuilder.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class RequestBuilder
{
    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param UrlBuilder $urlBuilder
     * @param string     $baseUrl
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(UrlBuilder $urlBuilder, $baseUrl)
    {
        $this->urlBuilder = $urlBuilder;

        $lastCharacter = strlen($baseUrl) - 1;
        if ('/' === $baseUrl[$lastCharacter]) {
            $baseUrl = substr($baseUrl, 0, $lastCharacter);
        }

        if ('http' !== substr($baseUrl, 0, 4)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected base URL to implement HTTP protocol. Should start with "http://" or "https://". Got "%s"'
                    .'instead.',
                    $baseUrl
                )
            );
        }

        $this->baseUrl = $baseUrl;
    }

    /**
     * Creates and return a new request object. All get, head, etc. methods are generated via this method.
     *
     * @example
     *  If URL is empty, only the base URL will be used:
     *  ::createRequest('GET')
     *  => http://localhost
     *
     *  If URI is used, add the base URL to generate the proper URL to request
     *  ::createRequest('GET', '/api/users')
     *  => http://localhost/api/users
     *
     *  If route name is used, will first generate the URI before applying the base URL; can use parameters
     *  ::createRequest('GET', 'users_cget')
     *  => http://localhost/api/users
     *
     *  ::createRequest('GET', 'users_get', null, ['parameters' => ['id' => 14]])
     *  => http://localhost/api/users/14
     *
     *  Can also apply other options
     *  ::createRequest('GET', null, null, ['query' => ['id' => 14, 'filter' => ['order' => ['startAt' => 'desc']]])
     *  => http://localhost/api?id=14&filter[where][name]=john
     *
     *  Or if you have just one query
     *  ::createRequest('GET', null, null, ['query' => 'filter[where][name]=john')
     *  => http://localhost/api?filter[where][name]=john
     *
     * @param string      $method  HTTP method.
     * @param string|null $url     URL,  URL, URI or route name.
     * @param string|null $token   API token.
     * @param array       $options Array of request options to apply. can have the keys:
     *                             - headers: array
     *                             - parameters (route parameters if $url is a route and not an URI)
     *                             - body: string|null
     *
     * @return RequestInterface
     *
     * @throws RouteNotFoundException              If the named route doesn't exist
     * @throws MissingMandatoryParametersException When some parameters are missing that are mandatory for the route
     * @throws InvalidParameterException           When a parameter value for a placeholder is not correct because it
     *                                             does not match the requirement
     */
    public function createRequest($method, $url = null, $token = null, array $options = [])
    {
        // Extract route parameters
        $parameters = [];
        if (true === array_key_exists('parameters', $options)) {
            $parameters = $options['parameters'];
        }

        // If Query is an array cast it to string
        $uri = null;
        if (true === array_key_exists('query', $options)) {
            if (true === is_array($options['query'])) {
                $uri = http_build_query($options['query']);
            } elseif (true === is_string($options['query'])) {
                $uri = $options['query'];
            }
        }

        // Build URL with query parameters
        $url = (null === $uri)
            ? $this->urlBuilder->buildUrl($this->baseUrl, $url, $parameters)
            : sprintf('%s?%s', $this->urlBuilder->buildUrl($this->baseUrl, $url, $parameters), $uri)
        ;

        return new Request($method, $url, $this->getHeaders($token, $options));
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Gets the request headers from the headers given in the options and add the authorization header if the API
     * token is provided.
     *
     * @param string|null $token   API token.
     * @param array       $options Array of request options
     *
     * @return string[]
     */
    private function getHeaders($token = null, array $options = [])
    {
        if (null !== $token) {
            $options['headers']['authorization'] = sprintf('Bearer %s', $token);
        }

        return (true === array_key_exists('headers', $options)) ? $options['headers'] : [];
    }
}
