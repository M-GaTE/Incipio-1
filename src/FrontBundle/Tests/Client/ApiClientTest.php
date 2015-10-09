<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Tests\Client;

use FrontBundle\Client\ApiClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Since the client heavily relies on Guzzle client, we only tests the added functionalities which are the token
 * handling and the generation of request via the route name instead of only the URI.
 *
 * @coversDefaultClass FrontBundle\Client\ApiClient
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 *
 * TODO: set more tests for the authorization token part, some errors have been noted while refactoring with the tests passing.
 */
class ApiClientTest extends KernelTestCase
{
    /**
     * @var ApiClient
     */
    private $service;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->service = self::$kernel->getContainer()->get('api.client');
    }

    /**
     * @dataProvider baseUrlProvider
     */
    public function testConstruct($baseUrl, $url)
    {
        $router = $this->prophesize(Router::class);
        $router->generate('my_route', [])->willReturn('/dummy');

        $guzzleClient = $this->prophesize(GuzzleClientInterface::class);
        $guzzleClient->getBaseUrl()->willReturn($baseUrl);
        $guzzleClient
            ->createRequest('GET', 'http://localhost/dummy', [])
            ->willReturn(new Request('GET', 'http://localhost/dummy'))
        ;
        $guzzleClient
            ->createRequest('GET', 'http://localhost', [])
            ->willReturn(new Request('GET', 'http://localhost'))
        ;

        $apiClient = new ApiClient($guzzleClient->reveal(), $router->reveal());
        $request = $apiClient->createRequest('GET', $url);

        if (true === empty($url)) {
            $this->assertEquals('http://localhost', $request->getUrl());
        } else {
            $this->assertEquals('http://localhost/dummy', $request->getUrl());
        }
    }

    /**
     * @covers ::send
     */
    public function testSendRequest()
    {
        $request = new Request('GET', 'http://localhost/dummy');

        $router = $this->prophesize(Router::class);
        $guzzleClient = $this->prophesize(GuzzleClientInterface::class);
        $guzzleClient->getBaseUrl()->willReturn('http://localhost');
        $guzzleClient->send($request)->willReturn(new Response(200));

        $apiClient = new ApiClient($guzzleClient->reveal(), $router->reveal());
        $response = $apiClient->send($request);

        $this->assertTrue($response instanceof ResponseInterface);
    }

    /**
     * @covers ::request
     */
    public function testRequest()
    {
        $request = new Request('GET', 'http://localhost/dummy');

        $router = $this->prophesize(Router::class);

        $guzzleClient = $this->prophesize(GuzzleClientInterface::class);
        $guzzleClient->getBaseUrl()->willReturn('http://localhost');
        $guzzleClient->createRequest('GET', 'http://localhost/dummy', [])->willReturn($request);
        $guzzleClient->send($request)->willReturn(new Response(200));

        $apiClient = new ApiClient($guzzleClient->reveal(), $router->reveal());
        $response = $apiClient->request('GET', 'http://localhost/dummy');

        $this->assertTrue($response instanceof ResponseInterface);
    }

    /**
     * @testdox      Check that the overridden methods generates the expected request
     *
     * @covers ::createRequest
     *
     * @dataProvider getMethodProvider
     *
     * @param string $url
     * @param bool   $token
     * @param array  $options
     * @param string $expectedUrl
     */
    public function testOverriddenMethod($url, $token, $options, $expectedUrl)
    {
        $tokenValue = (true === $token) ? 'MyToken' : null;

        $requests = [
            'get' => $this->service->createRequest('GET', $url, $tokenValue, $options),
            'head' => $this->service->createRequest('HEAD', $url, $tokenValue, $options),
            'delete' => $this->service->createRequest('DELETE', $url, $tokenValue, $options),
            'put' => $this->service->createRequest('PUT', $url, $tokenValue, $options),
            'post' => $this->service->createRequest('POST', $url, $tokenValue, $options),
        ];

        foreach ($requests as $request) {
            $this->assertTrue($request instanceof RequestInterface);

            $this->assertEquals(
                sprintf('http://localhost%s', $expectedUrl),
                urldecode($request->getUrl())
            );

            $headerCount = 2;
            if (null !== $tokenValue) {
                $this->assertTrue($request->hasHeader('authorization'));
                $this->assertEquals('Bearer MyToken', $request->getHeader('authorization'));
                ++$headerCount;
            } else {
                $this->assertFalse($request->hasHeader('authorization'));
            }

            if (isset($options['headers'])) {
                foreach ($options['headers'] as $header => $value) {
                    $this->assertTrue($request->hasHeader($header));
                    $this->assertEquals($value, $request->getHeader($header));
                }
                $headerCount += count($options['headers']);
            }

            $this->assertEquals($headerCount, count($request->getHeaders()));
        }
    }

    public function baseUrlProvider()
    {
        return [
            ['http://localhost', 'my_route'],
            ['http://localhost', '/dummy'],
            ['http://localhost', 'http://localhost/dummy'],
            ['http://localhost/', 'my_route'],
            ['http://localhost/', '/dummy'],
            ['http://localhost/', 'http://localhost/dummy'],
            ['http://localhost/', ''],
            ['http://localhost/', null],
        ];
    }

    public function getMethodProvider()
    {
        return [
            [
                null,
                false,
                [
                    'parameters' => ['id' => 14],
                ],
                '',
            ],
            // route name with parameters
            // no token
            // no options
            [
                'api_users_get',
                false,
                [
                    'parameters' => ['id' => 14],
                ],
                '/api/users/14',
            ],
            // route name
            // no token
            // no options
            [
                'api_users_cget',
                false,
                [],
                '/api/users',
            ],
            // route name
            // no token
            // options
            [
                'api_users_cget',
                false,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                ],
                '/api/users?random=test',
            ],
            // route name with parameters
            // token
            // no options
            [
                'api_users_get',
                true,
                [
                    'parameters' => ['id' => 14],
                ],
                '/api/users/14',
            ],
            // route name
            // token
            // no options
            [
                'api_users_cget',
                true,
                [],
                '/api/users',
            ],
            // route name with parameters
            // no token
            // no options
            [
                'api_users_get',
                false,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                    'parameters' => ['id' => 14],
                ],
                '/api/users/14?random=test',
            ],
            // route name
            // token
            // options
            [
                'api_users_cget',
                true,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                ],
                '/api/users?random=test',
            ],
            // URI
            // no token
            // no options
            [
                '/api/users',
                false,
                [],
                '/api/users',
            ],
            // URI
            // no token
            // options
            [
                '/api/users',
                false,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                ],
                '/api/users?random=test',
            ],
            // URI
            // token
            // no options
            [
                '/api/users',
                true,
                [],
                '/api/users',
            ],
            // URI
            // token
            // options
            [
                '/api/users',
                true,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                ],
                '/api/users?random=test',
            ],
            // URI with params
            // no token
            // no options
            [
                '/api/users?lol',
                false,
                [],
                '/api/users?lol',
            ],
            // URI with params
            // no token
            // options
            [
                '/api/users?lol',
                false,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                ],
                '/api/users?lol&random=test',
            ],
            // URI with params
            // token
            // no options
            [
                '/api/users?lol',
                true,
                [],
                '/api/users?lol',
            ],
            // URI with params
            // token
            // options
            [
                '/api/users?lol',
                true,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                ],
                '/api/users?lol&random=test',
            ],
            // URI with params
            // token
            // options with ignored parameters
            [
                '/api/users?lol',
                true,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                    'parameters' => ['id' => 14],
                ],
                '/api/users?lol&random=test',
            ],
            // URL with params
            // token
            // options
            [
                'http://localhost/api/users?lol',
                true,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                ],
                '/api/users?lol&random=test',
            ],
            // URL with params
            // token
            // options with ignored parameters
            [
                'http://localhost/api/users?lol',
                true,
                [
                    'query' => ['random' => 'test'],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                    'parameters' => ['id' => 14],
                ],
                '/api/users?lol&random=test',
            ],
            // URL with params inline
            // token
            // options with ignored parameters
            // Full array
            [
                'http://localhost/api/users?lol',
                true,
                [
                    'query' => ['id' => 14, 'filter' => ['order' => ['startAt' => 'desc']]],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                    'parameters' => ['id' => 14],
                ],
                '/api/users?lol&id=14&filter[order][startAt]=desc',
            ],
            // Partial array
            [
                'http://localhost/api/users?lol',
                true,
                [
                    'query' => ['id' => 14, 'filter[order][startAt]=desc' => null],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                    'parameters' => ['id' => 14],
                ],
                '/api/users?lol&id=14&filter[order][startAt]=desc',
            ],
            // Conflicting partials
            [
                'http://localhost/api/users?lol',
                true,
                [
                    'query' => [
                        'id' => 14,
                        'filter[order][startAt]=desc' => null,
                        'filter' => ['order' => ['endAt' => 'asc']],
                    ],
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                    'parameters' => ['id' => 14],
                ],
                '/api/users?lol&id=14&filter[order][startAt]=desc&filter[order][endAt]=asc',
            ],
            // Query is string
            [
                'http://localhost/api/users?lol',
                true,
                [
                    'query' => 'filter[order][startAt]=desc',
                    'headers' => ['Foo' => 'Bar', 'Baz' => 'Bam'],
                    'parameters' => ['id' => 14],
                ],
                '/api/users?lol&filter[order][startAt]=desc',
            ],
        ];
    }
}
