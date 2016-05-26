<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Manager;

/**
 * Interface for custom entity managers. This interface is not responsible for methods relative to the persistence
 * usually set in a Doctrine entity manager.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface NonPersistentEntityManagerInterface
{
    /**
     * Deletes the entity.
     *
     * @param object $entity
     */
    public function remove($entity);

    /**
     * Updates the entity.
     *
     * @param object $entity
     */
    public function update($entity);

    /**
     * Checks whether the given class is supported by this manager.
     *
     * @param object|string $entity Entity object of its FQCN.
     *
     * @return bool
     */
    public function supports($entity);
}
