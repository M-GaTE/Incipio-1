<?php

/*
 * This file is part of the DunglasApiBundle package.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\EventListener;

use ApiBundle\Doctrine\ORM\Manager\JobManager;
use ApiBundle\Entity\Job;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Call {@see ApiBundle\Doctrine\ORM\Manager\JobManager} when dealing with {@see ApiBundle\Entity\Job} entities.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class EntityManagersListener
{
    /**
     * @var JobManager
     */
    private $jobManager;

    public function __construct(JobManager $jobManager)
    {
        $this->jobManager = $jobManager;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     *
     * @return mixed
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $job = $event->getControllerResult();

        if (false === $job instanceof Job) {
            return $job;
        }

        switch ($event->getRequest()->getMethod()) {

            case Request::METHOD_POST:
            case Request::METHOD_PUT:
                $this->jobManager->updateAbbreviation($job);
                break;
        }
    }
}
