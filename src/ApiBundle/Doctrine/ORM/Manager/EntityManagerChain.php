<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Doctrine\ORM\Manager;

/**
 * A chain of entity managers.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class EntityManagerChain implements EntityManagerInterface
{
    /**
     * @var EntityManagerInterface[]
     */
    private $managers;

    /**
     * @param EntityManagerInterface[] $managers
     */
    public function __construct(array $managers)
    {
        $this->managers = $managers;
    }

    /**
     * Deletes the entity.
     *
     * @param $entity
     */
    public function delete($entity)
    {
        foreach ($this->managers as $manager) {
            if (true === $manager->supports($entity)) {
                $manager->delete($entity);
            }
        }
    }

    /**
     * Updates the entity.
     *
     * @param $entity
     */
    public function update($entity)
    {
        foreach ($this->managers as $manager) {
            if (true === $manager->supports($entity)) {
                $manager->update($entity);
            }
        }
    }

    /**
     * Checks whether the given class is supported by this manager.
     *
     * @param $entity
     *
     * @return boolean
     */
    public function supports($entity)
    {
        return true;
    }
}
