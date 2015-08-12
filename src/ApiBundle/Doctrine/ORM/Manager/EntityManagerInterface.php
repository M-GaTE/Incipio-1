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
 * Interface for custom entity managers.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface EntityManagerInterface
{
    /**
     * Deletes the entity.
     *
     * @param $entity
     */
    public function delete($entity);

    /**
     * Updates the entity.
     *
     * @param $entity
     */
    public function update($entity);

    /**
     * Checks whether the given class is supported by this manager.
     *
     * @param $entity
     *
     * @return bool
     */
    public function supports($entity);
}
