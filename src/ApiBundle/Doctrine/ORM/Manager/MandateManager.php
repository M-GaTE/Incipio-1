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

use ApiBundle\Entity\Mandate;
use Doctrine\ORM\Decorator\EntityManagerDecorator;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MandateManager extends EntityManagerDecorator implements EntityManagerInterface
{
    /**
     * Deletes the entity.
     *
     * @param Mandate $entity
     */
    public function delete($entity)
    {
        /** @var Mandate $entity */
        foreach ($entity->getJobs() as $job) {
            $job->setMandate(null);
        }
    }

    /**
     * Updates the entity.
     *
     * @param Mandate $entity
     */
    public function update($entity)
    {
        $this->updateName($entity);
    }

    /**
     * Checks whether the given class is supported by this manager.
     *
     * @param $entity
     *
     * @return bool
     */
    public function supports($entity)
    {
        return $entity instanceof Mandate;
    }

    /**
     * Updates Mandate name: if there is no name, one is generated.
     *
     * @param Mandate $mandate
     */
    public function updateName(Mandate $mandate)
    {
        if (false === empty($mandate->getName())) {
            return;
        }

        if (null !== $mandate->getEndAt() && $mandate->getStartAt()->format('Y') !== $mandate->getEndAt()->format('Y')) {
            $name = sprintf('Mandate %s/%s', $mandate->getStartAt()->format('Y'), $mandate->getEndAt()->format('Y'));
        } else {
            $name = sprintf('Mandate %s %s', $mandate->getStartAt()->format('m'), $mandate->getStartAt()->format('Y'));
        }

        $mandate->setName($name);
    }
}
