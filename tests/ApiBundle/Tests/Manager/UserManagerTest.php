<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Tests\Doctrine\ORM;

use ApiBundle\Entity\Job;
use ApiBundle\Entity\User;
use ApiBundle\Manager\UserManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * @coversDefaultClass ApiBundle\Manager\UserManager
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::remove
     * @covers ::deleteUser
     */
    public function testDelete()
    {
        $userClassMetadataProphecy = $this->prophesize(ClassMetadata::class);
        $userClassMetadataProphecy->getName()->willReturn(User::class);

        $userRepository = $this->prophesize(EntityRepository::class)->reveal();

        $encoderFactory = $this->prophesize(EncoderFactoryInterface::class)->reveal();
        $usernameCanonicalizerInterface = $this->prophesize(CanonicalizerInterface::class)->reveal();
        $emailCanonicalizerInterface = $this->prophesize(CanonicalizerInterface::class)->reveal();

        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $objectManagerProphecy->getRepository(User::class)->willReturn($userRepository);
        $objectManagerProphecy->getClassMetadata(User::class)->willReturn($userClassMetadataProphecy->reveal());

        $userManager = new UserManager(
            $encoderFactory,
            $usernameCanonicalizerInterface,
            $emailCanonicalizerInterface,
            $objectManagerProphecy->reveal(),
            User::class
        );

        $userWithJobs = new User();

        $firstJobProphecy = $this->prophesize(Job::class);
        $firstJobProphecy->getUsers()->willReturn(new ArrayCollection());
        $firstJobProphecy->addUser($userWithJobs)->shouldBeCalledTimes(1);
        $firstJobProphecy->removeUser($userWithJobs)->shouldBeCalledTimes(1);

        $secondJobProphecy = $this->prophesize(Job::class);
        $secondJobProphecy->getUsers()->willReturn(new ArrayCollection());
        $secondJobProphecy->addUser($userWithJobs)->shouldBeCalledTimes(1);
        $secondJobProphecy->removeUser($userWithJobs)->shouldBeCalledTimes(1);

        $userWithJobs = $userWithJobs
            ->addJob($firstJobProphecy->reveal())
            ->addJob($secondJobProphecy->reveal())
        ;

        $userWithoutJobs = new User();

        $userManager->remove($userWithJobs);
        $userManager->remove($userWithoutJobs);
    }

    /**
     * @covers ::supports
     */
    public function testSupports()
    {
        $userClassMetadataProphecy = $this->prophesize(ClassMetadata::class);
        $userClassMetadataProphecy->getName()->willReturn(User::class);

        $userRepository = $this->prophesize(EntityRepository::class)->reveal();

        $encoderFactory = $this->prophesize(EncoderFactoryInterface::class)->reveal();
        $usernameCanonicalizerInterface = $this->prophesize(CanonicalizerInterface::class)->reveal();
        $emailCanonicalizerInterface = $this->prophesize(CanonicalizerInterface::class)->reveal();

        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $objectManagerProphecy->getRepository(User::class)->willReturn($userRepository);
        $objectManagerProphecy->getClassMetadata(User::class)->willReturn($userClassMetadataProphecy->reveal());

        $userManager = new UserManager(
            $encoderFactory,
            $usernameCanonicalizerInterface,
            $emailCanonicalizerInterface,
            $objectManagerProphecy->reveal(),
            User::class
        );

        $this->assertTrue($userManager->supports($this->prophesize(UserInterface::class)->reveal()));
        $this->assertTrue($userManager->supports(UserInterface::class));
        $this->assertTrue($userManager->supports(new User()));
        $this->assertTrue($userManager->supports(User::class));

        $this->assertFalse($userManager->supports(Job::class));
        $this->assertFalse($userManager->supports(get_class(new \stdClass())));
    }

    /**
     * @covers ::updateCanonicalFields
     */
    public function testUpdateCanonicalFields()
    {
        $user = (new User())
            ->setEmail('email@example.com')
            ->setUsername('My Username')
            ->setOrganizationEmail('organization@example.com')
        ;
        $class = get_class($user);

        $encoderFactory = $this->prophesize(EncoderFactoryInterface::class);
        $usernameCanonicalizerProphecy = $this->prophesize(CanonicalizerInterface::class);
        $usernameCanonicalizerProphecy->canonicalize('My Username')->willReturn('canonicalUsername');
        $emailCanonicalizerProphecy = $this->prophesize(CanonicalizerInterface::class);
        $emailCanonicalizerProphecy->canonicalize('email@example.com')->willReturn('canonicalEmail');
        $emailCanonicalizerProphecy->canonicalize('organization@example.com')->willReturn('canonicalOrganisationEmail');

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getName()->willReturn($class);

        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $objectManagerProphecy->getRepository($class)->willReturn(EntityRepository::class);
        $objectManagerProphecy->getClassMetadata($class)->willReturn($metadataProphecy->reveal());

        $userManager = new UserManager(
            $encoderFactory->reveal(),
            $usernameCanonicalizerProphecy->reveal(),
            $emailCanonicalizerProphecy->reveal(),
            $objectManagerProphecy->reveal(),
            $class
        );

        $userManager->updateCanonicalFields($user);

        $this->assertEquals('email@example.com', $user->getEmail());
        $this->assertEquals('canonicalEmail', $user->getEmailCanonical());
        $this->assertEquals('organization@example.com', $user->getOrganizationEmail());
        $this->assertEquals('canonicalOrganisationEmail', $user->getOrganizationEmailCanonical());
        $this->assertEquals('My Username', $user->getUsername());
        $this->assertEquals('canonicalUsername', $user->getUsernameCanonical());
    }

    /**
     * @covers ::update
     * @covers ::updateUser
     */
    public function testUpdate()
    {
        $userWithUsername = (new User())
            ->setEmail('email@example.com')
            ->setUsername('My Username')
            ->setOrganizationEmail('organization@example.com')
        ;
        $userWithoutUsername = (new User())
            ->setEmail('email@example.com')
            ->setOrganizationEmail('organization@example.com')
        ;
        $class = get_class($userWithUsername);

        $encoderFactory = $this->prophesize(EncoderFactoryInterface::class);
        $usernameCanonicalizerProphecy = $this->prophesize(CanonicalizerInterface::class);
        $usernameCanonicalizerProphecy->canonicalize('My Username')->willReturn('canonicalUsername');
        $usernameCanonicalizerProphecy->canonicalize('email')->willReturn('canonicalUsername');
        $emailCanonicalizerProphecy = $this->prophesize(CanonicalizerInterface::class);
        $emailCanonicalizerProphecy->canonicalize('email@example.com')->willReturn('canonicalEmail');
        $emailCanonicalizerProphecy->canonicalize('organization@example.com')->willReturn('canonicalOrganisationEmail');

        $metadataProphecy = $this->prophesize(ClassMetadata::class);
        $metadataProphecy->getName()->willReturn($class);

        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $objectManagerProphecy->getRepository($class)->willReturn(EntityRepository::class);
        $objectManagerProphecy->getClassMetadata($class)->willReturn($metadataProphecy->reveal());
        $objectManagerProphecy->persist($userWithUsername)->shouldBeCalledTimes(1);
        $objectManagerProphecy->persist($userWithoutUsername)->shouldBeCalledTimes(1);

        $userManager = new UserManager(
            $encoderFactory->reveal(),
            $usernameCanonicalizerProphecy->reveal(),
            $emailCanonicalizerProphecy->reveal(),
            $objectManagerProphecy->reveal(),
            $class
        );

        $userManager->update($userWithUsername);
        $userManager->update($userWithoutUsername);

        $this->assertEquals('email@example.com', $userWithUsername->getEmail());
        $this->assertEquals('canonicalEmail', $userWithUsername->getEmailCanonical());
        $this->assertEquals('organization@example.com', $userWithUsername->getOrganizationEmail());
        $this->assertEquals('canonicalOrganisationEmail', $userWithUsername->getOrganizationEmailCanonical());
        $this->assertEquals('My Username', $userWithUsername->getUsername());
        $this->assertEquals('canonicalUsername', $userWithUsername->getUsernameCanonical());

        $this->assertEquals('email@example.com', $userWithoutUsername->getEmail());
        $this->assertEquals('canonicalEmail', $userWithoutUsername->getEmailCanonical());
        $this->assertEquals('organization@example.com', $userWithoutUsername->getOrganizationEmail());
        $this->assertEquals('canonicalOrganisationEmail', $userWithoutUsername->getOrganizationEmailCanonical());
        $this->assertEquals('email', $userWithoutUsername->getUsername());
        $this->assertEquals('canonicalUsername', $userWithoutUsername->getUsernameCanonical());
    }
}
