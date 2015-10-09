<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Tests\DependencyInjection;

use ApiBundle\DependencyInjection\ApiExtension;
use PHPUnit\Prophecy\DependencyInjectionArgument;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * @coversDefaultClass ApiBundle\DependencyInjection\ApiExtension
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class ApiExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover ::__construct
     */
    public function testConstruct()
    {
        $extension = new ApiExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
        $this->assertInstanceOf(ConfigurationExtensionInterface::class, $extension);
    }

    /**
     * @testdox Ensure that the Bundle extension load properly.
     *
     * @covers ::load
     */
    public function testLoading()
    {
        $extension = new ApiExtension();

        $containerBuilderProphecy = $this->getBaseDefaultContainerBuiderProphecy();
        $containerBuilderProphecy->setDefinition(Argument::any(), Argument::any())->shouldBeCalled();

        $extension->load([], $containerBuilderProphecy->reveal());
    }

    /**
     * Gets a Prophecy object for the ContainerBuilder which includes the mandatory called on the services included in
     * the default config.
     *
     * @return ObjectProphecy
     */
    private function getBaseDefaultContainerBuiderProphecy()
    {
        $containerBuilderProphecy = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');

        $containerBuilderProphecy
            ->addResource(DependencyInjectionArgument::service(getcwd().'/src/ApiBundle/Resources/config/services.yml'))
            ->shouldBeCalled()
        ;

        return $containerBuilderProphecy;
    }
}
