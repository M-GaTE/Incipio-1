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

use ApiBundle\Entity\Mandate;
use ApiBundle\EventListener\Doctrine\MandateListener;
use ApiBundle\Manager\NonPersistentEntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;

/**
 * @coversDefaultClass ApiBundle\EventListener\Doctrine\MandateListener
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MandateListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructWithSupportedEntity()
    {
        $manager = $this->prophesize(NonPersistentEntityManagerInterface::class);
        $manager->supports(Mandate::class)->willReturn(true);

        new MandateListener($manager->reveal());

        $this->assertTrue(true);
    }

    /**
     * @covers ::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testConstructWithUnsupportedEntity()
    {
        $manager = $this->prophesize(NonPersistentEntityManagerInterface::class);
        $manager->supports(Mandate::class)->willReturn(false);

        new MandateListener($manager->reveal());

        $this->assertTrue(true);
    }

    /**
     * @covers ::preFlush
     */
    public function testPreFlush()
    {
        $mandate = new Mandate();

        $manager = $this->prophesize(NonPersistentEntityManagerInterface::class);
        $manager->supports(Mandate::class)->willReturn(true);
        $manager->update($mandate)->shouldBeCalledTimes(1);

        $listener = new MandateListener($manager->reveal());

        $listener->preFlush($mandate, $this->prophesize(PreFlushEventArgs::class)->reveal());
    }

    /**
     * @covers ::preRemove
     */
    public function testPreRemove()
    {
        $mandate = new Mandate();

        $manager = $this->prophesize(NonPersistentEntityManagerInterface::class);
        $manager->supports(Mandate::class)->willReturn(true);
        $manager->remove($mandate)->shouldBeCalledTimes(1);

        $listener = new MandateListener($manager->reveal());

        $listener->preRemove($mandate, $this->prophesize(LifecycleEventArgs::class)->reveal());
    }
}
