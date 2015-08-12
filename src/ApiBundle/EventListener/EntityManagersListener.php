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

use ApiBundle\Doctrine\ORM\Manager\EntityManagerChain;
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
     * @param GetResponseForControllerResultEvent $event
     *
     * @return mixed
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $controllerResult = $event->getControllerResult();

        switch ($event->getRequest()->getMethod()) {

            case Request::METHOD_POST:
            case Request::METHOD_PUT:
                $this->entityManagerChain->update($controllerResult);
                break;

            case Request::METHOD_DELETE:
                $this->entityManagerChain->delete($controllerResult);
                break;
        }
    }
}
