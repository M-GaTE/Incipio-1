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

use ApiBundle\Entity\Job;
use Doctrine\ORM\Decorator\EntityManagerDecorator;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class JobManager extends EntityManagerDecorator implements EntityManagerInterface
{
    /**
     * Deletes the entity.
     *
     * @param Job $entity
     */
    public function delete($entity)
    {
    }

    /**
     * Updates the entity.
     *
     * @param Job $entity
     */
    public function update($entity)
    {
        $this->updateAbbreviation($entity);
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
        return $entity instanceof Job;
    }

    /**
     * Updates Job abbreviation: if there is no abbreviation, one is created from the title.
     *
     * @param Job $job
     */
    public function updateAbbreviation(Job $job)
    {
        if (false === empty($job->getAbbreviation())) {
            return;
        }

        $abbreviation = '';
        $parts = explode(' ', $job->getTitle());

        if (1 === count($parts)) {
            $abbreviation = substr($parts[0], 0, 4);
        } else {
            foreach ($parts as $part) {
                $abbreviation .= $part[0];
            }
        }

        $job->setAbbreviation($abbreviation);
    }
}
