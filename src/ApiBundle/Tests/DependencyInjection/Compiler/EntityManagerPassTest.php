<?php

namespace ApiBundle\Tests\DependencyInjection\Compiler;

use ApiBundle\DependencyInjection\Compiler\EntityManagerPass;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @coversDefaultClass ApiBundle\DependencyInjection\Compiler\EntityManagerPass
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class EntityManagerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::process
     */
    public function testProcess()
    {
        $entityManagerPass = new EntityManagerPass();

        $this->assertInstanceOf(CompilerPassInterface::class, $entityManagerPass);

        $definitionProphecy = $this->prophesize(Definition::class);
        $definitionProphecy->addArgument(Argument::type('array'))->shouldBeCalled();
        $definition = $definitionProphecy->reveal();

        $containerBuilderProphecy = $this->prophesize(ContainerBuilder::class);
        $containerBuilderProphecy->findTaggedServiceIds('api.entity_manager')->willReturn([
            'foo' => [],
            'bar' => ['priority' => 1]
        ])->shouldBeCalled();
        $containerBuilderProphecy->getDefinition('api.entity_manager_chain')->willReturn($definition)->shouldBeCalled();
        $containerBuilder = $containerBuilderProphecy->reveal();

        $entityManagerPass->process($containerBuilder);
    }
}
