<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\DataFixtures\Processor;

use ApiBundle\Doctrine\ORM\Manager\EntityManagerChain;
use Nelmio\Alice\ProcessorInterface;

/**
 * Is called before and after persisting the fixtures loaded to allow custom manipulation on the loaded objects. Here
 * ensure all entities are updated by their matching EntityManager.
 *
 * @link   https://github.com/nelmio/alice/blob/master/doc/processors.md#processors
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class EntityManagersProcessor implements ProcessorInterface
{
    /**
     * @var EntityManagerChain
     */
    private $entityManagerChain;

    /**
     * @param EntityManagerChain $entityManagerChain
     */
    public function __construct(EntityManagerChain $entityManagerChain)
    {
        $this->entityManagerChain = $entityManagerChain;
    }

    /**
     * {@inheritdoc}
     */
    public function preProcess($object)
    {
        $this->entityManagerChain->update($object);
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess($object)
    {
    }
}
