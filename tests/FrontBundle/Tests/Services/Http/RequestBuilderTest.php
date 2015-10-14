<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Tests\Services\Http;

use FrontBundle\Services\Http\RequestBuilder;
use FrontBundle\Services\Http\UrlBuilder;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @coversDefaultClass FrontBundle\Services\Http\RequestBuilder
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class RequestBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstructor()
    {
        $urlBuilderProphecy = $this->prophesize(UrlBuilder::class);

        $requestBuilder = new RequestBuilder($urlBuilderProphecy->reveal(), 'http://example.com');
        $this->assertEquals('http://example.com', $requestBuilder->getBaseUrl());

        $requestBuilder = new RequestBuilder($urlBuilderProphecy->reveal(), 'http://example.com/');
        $this->assertEquals('http://example.com', $requestBuilder->getBaseUrl());

        try {
            new RequestBuilder($urlBuilderProphecy->reveal(), 'example.com');
            $this->fail('Expected exception to be thrown');
        } catch (\InvalidArgumentException $exception) {
            // Expected result
        }
    }

    /**
     * @cover ::createRequest
     */
    public function testCreateRequest()
    {
        $routerProphecy = $this->prophesize(RouterInterface::class);
        $routerProphecy->generate('users_cget', [])->willReturn('/api/users');
        $routerProphecy->generate('users_get', ['id' => 14])->willReturn('/api/users/14');

        $urlBuilder = new UrlBuilder($routerProphecy->reveal(), 'http://example.com');

        $requestBuilder = new RequestBuilder($urlBuilder, 'http://example.com');

        $request = new Request('GET', 'http://example.com');
        $this->assertEquals($request, $requestBuilder->createRequest('GET'));

        $request = new Request('GET', 'http://example.com/api/users');
        $this->assertEquals($request, $requestBuilder->createRequest('GET', '/api/users'));
        $this->assertEquals($request, $requestBuilder->createRequest('GET', 'users_cget'));

        $request = new Request('GET', 'http://example.com/api/users/14');
        $this->assertEquals(
            $request,
            $requestBuilder->createRequest('GET', 'users_get', null, ['parameters' => ['id' => 14]])
        );

        $request = new Request('GET', 'http://example.com/api?id=14&filter[where][name]=john');
        $this->assertEquals(
            $request,
            $requestBuilder->createRequest('GET',
                '/api',
                null,
                [
                    'query' => [
                        'id' => 14,
                        'filter' => [
                            'where' => [
                                'name' => 'john',
                            ],
                        ],
                    ],
                ]
            )
        );
        $this->assertEquals(
            $request,
            $requestBuilder->createRequest('GET',
                '/api',
                null,
                [
                    'query' => 'id=14&filter[where][name]=john',
                ]
            )
        );

        $request = new Request('GET', 'http://example.com', ['dummyHeader' => 'some value']);
        $this->assertEquals(
            $request,
            $requestBuilder->createRequest('GET', null, null, ['headers' => ['dummyHeader' => 'some value']])
        );

        $request = new Request('GET', 'http://example.com', ['authorization' => 'Bearer MyToken']);
        $this->assertEquals(
            $request,
            $requestBuilder->createRequest('GET', null, 'MyToken')
        );

        $request = new Request('GET',
            'http://example.com',
            ['dummyHeader' => 'some value', 'authorization' => 'Bearer MyToken']);
        $this->assertEquals(
            $request,
            $requestBuilder->createRequest('GET', null, 'MyToken', ['headers' => ['dummyHeader' => 'some value']])
        );
    }
}
