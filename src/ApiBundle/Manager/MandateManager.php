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

use ApiBundle\Entity\Mandate;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MandateManager implements NonPersistentEntityManagerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param Mandate $entity
     */
    public function remove($entity)
    {
        foreach ($entity->getJobs() as $job) {
            $job->setMandate(null);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param Mandate $entity
     */
    public function update($entity)
    {
        $this->updateName($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity)
    {
        if (is_string($entity)) {
            return Mandate::class === $entity;
        }

        return $entity instanceof Mandate;
    }

    /**
     * Updates Mandate name: if there is no name, one is generated.
     *
     * @param Mandate $mandate
     */
    public function updateName(Mandate $mandate)
    {
        if (false === empty($mandate->getName())
            || true === empty($mandate->getStartAt())
        ) {
            return;
        }

        if (null !== $mandate->getEndAt()
            && $mandate->getStartAt()->format('Y') !== $mandate->getEndAt()->format('Y')
        ) {
            $name = sprintf('Mandate %s/%s', $mandate->getStartAt()->format('Y'), $mandate->getEndAt()->format('Y'));
        } else {
            $name = sprintf('Mandate %s %s', $mandate->getStartAt()->format('m'), $mandate->getStartAt()->format('Y'));
        }

        $mandate->setName($name);
    }
}
