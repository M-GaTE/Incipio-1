<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Tests\Entity;

use ApiBundle\Entity\Job;
use ApiBundle\Entity\Mandate;
use ApiBundle\Entity\User;
use PHPUnit\Framework\AbstractEntityTestCase;

/**
 * @coversDefaultClass ApiBundle\Entity\Job
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class JobTest extends AbstractEntityTestCase
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClassName()
    {
        return Job::class;
    }

    /**
     * {@inheritdoc}
     *
     * @covers ::getId
     * @covers ::setTitle
     * @covers ::getTitle
     * @covers ::setAbbreviation
     * @covers ::getAbbreviation
     * @covers ::setEnabled
     * @covers ::getEnabled
     * @covers ::addUser
     * @covers ::removeUser
     * @covers ::getUsers
     * @covers ::setMandate
     * @covers ::getMandate
     * @dataProvider propertyAccessorProvider
     */
    public function testPropertyAccessors(array $data = [])
    {
        /** @var Mandate $mandate */
        $mandate = $data['mandate'];

        $job = (new Job())
            ->setTitle($data['title'])
            ->setAbbreviation($data['abbreviation'])
            ->setEnabled($data['enabled'])
            ->setMandate($mandate);
        foreach ($data['users'] as $user) {
            // Is added two times to ensure the adder handles duplications and will add it only one time
            $job->addUser($user);
            $job->addUser($user);
            $this->doctrineManager->persist($user);
        }

        $this->doctrineManager->persist($job);
        $this->doctrineManager->persist($mandate);
        $this->doctrineManager->flush();

        // Test classic setters
        $this->assertNotNull($job->getId());
        $this->assertEquals($data['title'], $job->getTitle());
        $this->assertEquals($data['abbreviation'], $job->getAbbreviation());
        $this->assertEquals($data['enabled'], $job->getEnabled());

        // Test users relationship
        $this->assertEquals(count($data['users']), count($job->getUsers()));
        foreach ($data['users'] as $user) {
            /* @var User $user */
            $this->assertTrue($job->getUsers()->contains($user));
            $this->assertTrue($user->getJobs()->contains($job));
        }

        // Test mandate relationship
        $this->assertEquals($mandate, $job->getMandate());
        $this->assertTrue($mandate->getJobs()->contains($job));
        $this->assertEquals(1, count($mandate->getJobs()));

        // Test if properties and relations can be reset
        $job
            ->setTitle(null)
            ->setAbbreviation(null)
            ->setEnabled(true)
            ->setMandate(null);
        foreach ($data['users'] as $user) {
            $job->removeUser($user);
        }

        $this->doctrineManager->flush();

        $this->assertNull($job->getTitle());
        $this->assertNull($job->getAbbreviation());
        $this->assertTrue($job->getEnabled());

        // Test users relationship
        $this->assertEquals(0, count($job->getUsers()));
        foreach ($data['users'] as $user) {
            /* @var User $user */
            $this->assertFalse($user->getJobs()->contains($job));
        }

        // Test mandate relationship
        $this->assertNull($job->getMandate());
        $this->assertFalse($mandate->getJobs()->contains($job));
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider propertyAccessorProvider
     */
    public function testDeleteEntity(array $data = [])
    {
        // Instantiate Job
        /** @var Mandate $mandate */
        $mandate = $data['mandate'];

        $job = (new Job())
            ->setTitle($data['title'])
            ->setAbbreviation($data['abbreviation'])
            ->setEnabled($data['enabled'])
            ->setMandate($mandate);
        foreach ($data['users'] as $user) {
            // Is added two times to ensure the adder handles duplications and will add it only one time
            $job->addUser($user);
            $job->addUser($user);
            $this->doctrineManager->persist($user);
        }

        $this->doctrineManager->persist($job);
        $this->doctrineManager->persist($mandate);
        $this->doctrineManager->flush();

        // Actual test
        $this->doctrineManager->remove($job);
        $this->doctrineManager->flush();
        foreach ($data['users'] as $user) {
            /* @var User $user */
            $this->assertFalse(
                $user->getJobs()->contains($job),
                'Expected $user instance to no longer have a reference to the job.'
            );
        }
        $this->assertFalse(
            $mandate->getJobs()->contains($job),
            'Expected $mandate instance to no longer have a reference to the job.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fluentDataProvider()
    {
        return [
            [
                [
                    'title' => 'President',
                    'abbreviation' => 'Pres',
                    'enabled' => true,
                    'users' => new User(),
                    'mandate' => new Mandate(),
                ],
            ],
        ];
    }

    public function propertyAccessorProvider()
    {
        return [
            [
                [
                    'title' => 'President',
                    'abbreviation' => 'Pres',
                    'enabled' => true,
                    'users' => [$this->getAUserInstance()],
                    'mandate' => $this->getAMandateInstance(),
                ],
            ],
        ];
    }

    /**
     * @return User
     */
    private function getAUserInstance()
    {
        $user = (new User())
            ->setEmail('dummy@example.com')
            ->setPassword('dummyPassword')
            ->setUsername('dummy.username');

        return $user;
    }

    /**
     * @return Mandate
     */
    private function getAMandateInstance()
    {
        $mandate = (new Mandate())
            ->setName('Dummy Mandate')
            ->setStartAt(new \DateTime());

        return $mandate;
    }

    /**
     * @coversNothing
     * @dataProvider validDataProvider
     */
    public function testValidationConstraintsWithValidData(array $data, $groups = null)
    {
        $this->markTestSkipped('TODO, see ::validDataProvider');
    }

    /**
     * @coversNothing
     * @dataProvider invalidDataProvider
     */
    public function testValidationConstraintsWithInvalidData(array $data, $groups = null)
    {
        $this->markTestSkipped('TODO, see ::invalidDataProvider');
    }

    /**
     * {@inheritdoc}
     *
     * @TODO
     */
    public function validDataProvider()
    {
        return [
            [[], 'tempo'],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @TODO
     */
    public function invalidDataProvider()
    {
        return [
            [[], 'tempo'],
        ];
    }
}
