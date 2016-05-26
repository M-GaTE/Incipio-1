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
use FrontBundle\Services\Http\RequestBuilder;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @coversDefaultClass FrontBundle\Client\ApiClient
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class ApiClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstruct()
    {
        $guzzleClientProphecy = $this->prophesize(GuzzleClientInterface::class);
        $guzzleClientProphecy->getConfig('base_url')->willReturn('http://localhost');

        $requestBuilderProphecy = $this->prophesize(RequestBuilder::class);
        $requestBuilderProphecy->getBaseUrl()->willReturn('http://localhost');

        new ApiClient($guzzleClientProphecy->reveal(), $requestBuilderProphecy->reveal());
        $this->assertTrue(true);

        $guzzleClientProphecy = $this->prophesize(GuzzleClientInterface::class);
        $guzzleClientProphecy->getConfig('base_url')->willReturn('http://localhost/api');

        $requestBuilderProphecy = $this->prophesize(RequestBuilder::class);
        $requestBuilderProphecy->getBaseUrl()->willReturn('http://localhost');

        try {
            new ApiClient($guzzleClientProphecy->reveal(), $requestBuilderProphecy->reveal());
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
        $guzzleClientProphecy = $this->prophesize(GuzzleClientInterface::class);
        $guzzleClientProphecy->getConfig('base_url')->willReturn('http://localhost');

        $request = $this->prophesize(RequestInterface::class)->reveal();

        $requestBuilderProphecy = $this->prophesize(RequestBuilder::class);
        $requestBuilderProphecy->getBaseUrl()->willReturn('http://localhost');
        $requestBuilderProphecy
            ->createRequest('GET', '/api', 'MyToken', ['something'])
            ->shouldBeCalledTimes(1)
            ->willReturn($request)
        ;

        $apiClient = new ApiClient($guzzleClientProphecy->reveal(), $requestBuilderProphecy->reveal());

        $this->assertSame($request, $apiClient->createRequest('GET', '/api', 'MyToken', ['something']));
    }

    /**
     * @cover ::send
     */
    public function testSendRequest()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $guzzleClientProphecy = $this->prophesize(GuzzleClientInterface::class);
        $guzzleClientProphecy
            ->send($request)
            ->shouldBeCalledTimes(1)
            ->willReturn($response)
        ;
        $guzzleClientProphecy->getConfig('base_url')->willReturn('http://localhost');

        $requestBuilderProphecy = $this->prophesize(RequestBuilder::class);
        $requestBuilderProphecy->getBaseUrl()->willReturn('http://localhost');

        $apiClient = new ApiClient($guzzleClientProphecy->reveal(), $requestBuilderProphecy->reveal());

        $this->assertSame(
            $response,
            $apiClient->send($request)
        );
    }

    /**
     * @cover ::request
     */
    public function testRequest()
    {
        $request = $this->prophesize(RequestInterface::class)->reveal();
        $response = $this->prophesize(ResponseInterface::class)->reveal();

        $guzzleClientProphecy = $this->prophesize(GuzzleClientInterface::class);
        $guzzleClientProphecy
            ->send($request)
            ->shouldBeCalledTimes(1)
            ->willReturn($response)
        ;
        $guzzleClientProphecy->getConfig('base_url')->willReturn('http://localhost');

        $requestBuilderProphecy = $this->prophesize(RequestBuilder::class);
        $requestBuilderProphecy->getBaseUrl()->willReturn('http://localhost');
        $requestBuilderProphecy
            ->createRequest('GET', '/api', 'MyToken', ['something'])
            ->shouldBeCalledTimes(1)
            ->willReturn($request)
        ;

        $apiClient = new ApiClient($guzzleClientProphecy->reveal(), $requestBuilderProphecy->reveal());

        $this->assertSame($response, $apiClient->request('GET', '/api', 'MyToken', ['something']));
    }
}
