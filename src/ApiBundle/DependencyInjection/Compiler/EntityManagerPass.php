<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Injects the entity managers into the chain.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class EntityManagerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $managers = [];
        foreach ($container->findTaggedServiceIds('api.entity_manager') as $serviceId => $tags) {
            $managers[] = new Reference($serviceId);
        }

        $container->getDefinition('api.entity_manager_chain')->addArgument($managers);
    }
}
