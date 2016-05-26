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

use FrontBundle\Client\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface ClientInterface
{
    /**
     * Creates and return a new {@see Request} object. All get, head, etc. methods are generated via this method.
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
     */
    public function createRequest($method, $url = null, $token = null, array $options = []);

    /**
     * Sends a request.
     *
     * @param string      $method  HTTP method
     * @param string|null $url     URL, URI or route name.
     * @param string|null $token   API token.
     * @param array       $options Options applied to the request.
     *
     * @return ResponseInterface
     *
     * @throws ClientException
     */
    public function request($method, $url = null, $token = null, $options = []);

    /**
     * Sends a single request.
     *
     * @param RequestInterface $request Request to send
     *
     * @return ResponseInterface
     *
     * @throws ClientException
     */
    public function send(RequestInterface $request);
}
