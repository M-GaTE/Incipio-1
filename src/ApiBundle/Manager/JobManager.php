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

use ApiBundle\Entity\Job;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class JobManager implements NonPersistentEntityManagerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param Job $entity
     */
    public function remove($entity)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param Job $entity
     */
    public function update($entity)
    {
        $this->updateAbbreviation($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity)
    {
        if (is_string($entity)) {
            return Job::class === $entity;
        }

        return $entity instanceof Job;
    }

    /**
     * Updates Job abbreviation by creating one from the title if no abbreviation exists (provided Job has already a
     * title).
     *
     * @param Job $job
     */
    public function updateAbbreviation(Job $job)
    {
        if (false === empty($job->getAbbreviation())
            || true === empty($job->getTitle())
        ) {
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
