<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Controller;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

/**
 * Interface registering all controller methods that should be refactoring into a base controller to provide helpers
 * and avoid duplicating code and that are specific to this application, i.e. methods that are not in {@see
 * Symfony\Bundle\FrameworkBundle\Controller\Controller} or which differs from it.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface ApiControllerInterface
{
    /**
     * Generates a URL from the given parameters.
     *
     * If the `id` key of parameters passed has an URI as a value, its ID is automatically extracted from it. This is
     * done my assuming that the ID is the last member of the URI and that and URI begins by `/`.
     *
     * @param string      $route         The name of the route
     * @param array       $parameters    An array of parameters
     * @param bool|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    public function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH);

    /**
     * Decodes a JSON string into PHP data.
     *
     * @param string $data    Data to decode
     * @param array  $context options that decoders have access to.
     *
     * @return array
     *
     * @throws UnexpectedValueException
     */
    public function decode($data, array $context = []);

    /**
     * Send a GET request for the client and decode its response body.
     *
     * @see FrontBundle\Client\ApiClientInterface::createRequest()
     *
     * @param string                               $method  HTTP method
     * @param string|null                          $url     URL, URI or route name.
     * @param Request|RequestInterface|string|null $token   API token. If request, will look into the session
     *                                                      for the API token.
     * @param array                                $options Options applied to the request.
     *
     * @return RequestInterface
     */
    public function createRequest($method, $url = null, $token = null, $options = []);

    /**
     * Send a GET request for the client and decode its response body.
     *
     * @see FrontBundle\Client\ApiClientInterface::request()
     * @see $this::decode
     *
     * @param string                               $method          HTTP method
     * @param string|null                          $url             URL, URI or route name.
     * @param Request|RequestInterface|string|null $token           API token. If request, will look into the session
     *                                                              for the API token.
     * @param array                                $options         Options applied to the request.
     * @param bool                                 $wholeCollection If set to true, will consider the response is a
     *                                                              paginated collection and will go through all pages
     *                                                              to return the complete list of entities. This
     *                                                              parameters is ignored if the response is not a
     *                                                              collection.
     *
     * @return array
     *
     * @throws \LogicException  When the handler does not populate a response
     * @throws RequestException When an error is encountered
     */
    public function requestAndDecode($method, $url = null, $token = null, $options = [], $wholeCollection = false);

    /**
     * Sends a single request and decode its response body.
     *
     * @see FrontBundle\Client\ApiClientInterface::send()
     * @see $this::decode
     *
     * @param RequestInterface $request         Request to send
     * @param bool             $wholeCollection If set to true, will consider the response is a paginated
     *                                          collection and will go through all pages to return the complete list of
     *                                          entities. This parameters is ignored if the response is not a
     *                                          collection.
     *
     * @return array
     *
     * @throws \LogicException          When the handler does not populate a response
     * @throws RequestException         When an error is encountered
     * @throws UnexpectedValueException
     */
    public function sendAndDecode(RequestInterface $request, $wholeCollection = false);

    /**
     * Handle Guzzle exceptions. Assumes the exception was thrown while sending a request to the API. For more
     * information regarding Guzzle exceptions, refer to {@link * http://guzzle.readthedocs.org/en/latest/quickstart.html#exceptions}.
     *
     * @param TransferException $exception
     */
    public function handleGuzzleException(TransferException $exception);
}
