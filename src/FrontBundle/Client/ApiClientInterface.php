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

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface ApiClientInterface
{
    /**
     * Create and return a new {@see RequestInterface} object. All get, head, etc. methods are generated via this
     * method.
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
     *  => http://localhost?id=14&filter[where][name]=john
     *
     *  But passing queries this way might be simpler
     *  ::createRequest('GET', null, null, ['query' => ['id' => 14, 'filter[where][name]=john' => null])
     *  => http://localhost?id=14&filter[where][name]=john
     *
     *  Or if you have just one query
     *  ::createRequest('GET', null, null, ['query' => 'filter[where][name]=john')
     *  => http://localhost?filter[where][name]=john
     *
     * @param string      $method  HTTP method.
     * @param string|null $url     URL,  URL, URI or route name.
     * @param string|null $token   API token.
     * @param array       $options Array of request options to apply.
     *
     * @return RequestInterface
     */
    public function createRequest($method, $url = null, $token = null, array $options = []);

    /**
     * Send a request.
     *
     * @param string      $method  HTTP method
     * @param string|null $url     URL, URI or route name.
     * @param string|null $token   API token.
     * @param array       $options Options applied to the request.
     *
     * @return ResponseInterface
     *
     * @throws \LogicException  When the handler does not populate a response
     * @throws RequestException When an error is encountered
     */
    public function request($method, $url = null, $token = null, $options = []);

    /**
     * Sends a single request.
     *
     * @param RequestInterface $request Request to send
     *
     * @return \GuzzleHttp\Message\ResponseInterface
     *
     * @throws \LogicException  When the handler does not populate a response
     * @throws RequestException When an error is encountered
     */
    public function send(RequestInterface $request);
}
