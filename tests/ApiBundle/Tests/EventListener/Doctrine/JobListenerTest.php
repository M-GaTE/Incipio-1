<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Tests\EventListener\Doctrine;

use ApiBundle\Entity\Job;
use ApiBundle\EventListener\Doctrine\JobListener;
use ApiBundle\Manager\NonPersistentEntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

/**
 * @coversDefaultClass ApiBundle\EventListener\Doctrine\JobListener
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class JobListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructWithSupportedEntity()
    {
        $manager = $this->prophesize(NonPersistentEntityManagerInterface::class);
        $manager->supports(Job::class)->willReturn(true);

        new JobListener($manager->reveal());

        $this->assertTrue(true);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testConstructWithUnsupportedEntity()
    {
        $manager = $this->prophesize(NonPersistentEntityManagerInterface::class);
        $manager->supports(Job::class)->willReturn(false);

        new JobListener($manager->reveal());

        $this->assertTrue(true);
    }

    /**
     * @covers ::preFlush
     */
    public function testPreFlush()
    {
        $job = new Job();

        $manager = $this->prophesize(NonPersistentEntityManagerInterface::class);
        $manager->supports(Job::class)->willReturn(true);
        $manager->update($job)->shouldBeCalledTimes(1);

        $listener = new JobListener($manager->reveal());

        $listener->preFlush($job, $this->prophesize(PreFlushEventArgs::class)->reveal());
    }

    /**
     * @covers ::preRemove
     */
    public function testPreRemove()
    {
        $job = new Job();

        $manager = $this->prophesize(NonPersistentEntityManagerInterface::class);
        $manager->supports(Job::class)->willReturn(true);
        $manager->remove($job)->shouldBeCalledTimes(1);

        $listener = new JobListener($manager->reveal());

        $listener->preRemove($job, $this->prophesize(LifecycleEventArgs::class)->reveal());
    }
}
