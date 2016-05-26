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

use FrontBundle\Services\Http\UrlBuilder;
use Prophecy\Argument;
use Symfony\Component\Routing\RouterInterface;

/**
 * @coversDefaultClass FrontBundle\Services\Http\UrlBuilder
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class UrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstructor()
    {
        $routerProphecy = $this->prophesize(RouterInterface::class);

        new UrlBuilder($routerProphecy->reveal());
        $this->assertTrue(true);
    }

    /**
     * @cover ::buildUrl
     * @dataProvider urlWithoutRouteProvider
     */
    public function testBuildUrlWithoutRoute($baseUrl, $uri, $expected)
    {
        $routerProphecy = $this->prophesize(RouterInterface::class);
        $routerProphecy->generate(Argument::any(), Argument::any())->shouldNotBeCalled();

        $urlBuilder = new UrlBuilder($routerProphecy->reveal());
        $url = $urlBuilder->buildUrl($baseUrl, $uri);

        $this->assertEquals(
            $expected,
            $url
        );
    }

    /**
     * @cover ::buildUrl
     * @dataProvider urlWithRouteProvider
     */
    public function testBuildUrlWithRoute($baseUrl, $uri, $parameters, $expected)
    {
        $routerProphecy = $this->prophesize(RouterInterface::class);
        $routerProphecy->generate('my_route', [])->willReturn('/my/route');
        $routerProphecy->generate('my_route', ['id' => 14])->willReturn('/my/route/14');

        $urlBuilder = new UrlBuilder($routerProphecy->reveal());

        $this->assertEquals(
            $expected,
            $urlBuilder->buildUrl($baseUrl, $uri, $parameters)
        );
    }

    public function urlWithoutRouteProvider()
    {
        return [
            [
                'http://localhost',
                null,
                'http://localhost',
            ],
            [
                'http://localhost/api',
                null,
                'http://localhost/api',
            ],
            [
                'http://localhost',
                '',
                'http://localhost',
            ],
            [
                'http://localhost',
                '/',
                'http://localhost/',
            ],
            [
                'http://localhost/',
                '/',
                'http://localhost/',
            ],
            [
                'http://localhost',
                '/uri',
                'http://localhost/uri',
            ],
            [
                'http://localhost/',
                '/uri',
                'http://localhost/uri',
            ],
            [
                'http://localhost',
                '/path/to',
                'http://localhost/path/to',
            ],
            [
                'http://localhost/',
                '/path/to',
                'http://localhost/path/to',
            ],
            [
                'http://localhost/',
                'http://something',
                'http://something',
            ],
        ];
    }

    public function urlWithRouteProvider()
    {
        return [
            [
                'http://localhost',
                'my_route',
                [],
                'http://localhost/my/route',
            ],
            [
                'http://localhost',
                'my_route',
                [
                    'id' => 14,
                ],
                'http://localhost/my/route/14',
            ],
        ];
    }
}
