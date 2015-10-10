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
use ApiBundle\Entity\User;
use PHPUnit\Framework\AbstractEntityTestCase;

/**
 * @coversDefaultClass ApiBundle\Entity\User
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class UserTest extends AbstractEntityTestCase
{
    /**
     * @var \FOS\UserBundle\Model\UserManagerInterface|\Doctrine\Common\Persistence\ObjectManager
     */
    private $userManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->userManager = static::$kernel->getContainer()->get('fos_user.user_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClassName()
    {
        return User::class;
    }

    /**
     * {@inheritdoc}
     *
     * @covers ::setUsername
     * @covers ::getUsername
     * @covers ::setFullname
     * @covers ::setEmail
     * @covers ::getEmail
     * @covers ::setRoles
     * @covers ::getRoles
     * @covers ::setPlainPassword
     * @covers ::getPlainPassword
     * @covers ::setEnabled
     * @covers ::isEnabled
     * @covers ::addJob
     * @covers ::removeJob
     * @covers ::setCreatedAt
     * @covers ::getCreatedAt
     * @dataProvider propertyAccessorProvider
     */
    public function testPropertyAccessors(array $data = [])
    {
        $user = (new User())
            ->setUsername($data['username'])
            ->setFullname($data['fullname'])
            ->setEmail($data['email'])
            ->setRoles($data['roles'])
            ->setPassword($data['plainPassword'])
            ->setEnabled($data['enabled'])
            ->setCreatedAt($data['createdAt'])
            ->setEndingSchoolYear($data['endingSchoolYear'])
        ;
        foreach ($data['jobs'] as $job) {
            // Is added two times to ensure the adder handles duplications and will add it only one time
            $user->addJob($job);
            $user->addJob($job);
            $this->doctrineManager->persist($job);
        }
        $this->doctrineManager->persist($user);
        $this->doctrineManager->flush();

        // Test classic setters
        $this->assertEquals($data['username'], $user->getUsername());
        $this->assertEquals($data['fullname'], $user->getFullname());
        $this->assertEquals($data['email'], $user->getEmail());
        foreach ($data['roles'] as $role) {
            $this->assertTrue(in_array($role, $user->getRoles()));
        }
        $this->assertEquals($data['plainPassword'], $user->getPassword());
        $this->assertEquals($data['enabled'], $user->isEnabled());
        $this->assertEquals($data['createdAt'], $user->getCreatedAt());
        $this->assertEquals($data['endingSchoolYear'], $user->getEndingSchoolYear());

        // Test job relationship
        $this->assertEquals(count($data['jobs']), count($user->getJobs()));
        foreach ($data['jobs'] as $job) {
            /* @var Job $job */
            $this->assertTrue($user->getJobs()->contains($job));
            $this->assertTrue($job->getUsers()->contains($user));
        }

        // Before any other reset/remove manipulation, count number of entities for collections
        // This is done to allow checking the removing removes only what is needed
        $counter = [
            'user' => [
                'jobs' => count($user->getJobs()),
            ],
            'jobs' => [],
        ];
        foreach ($data['jobs'] as $job) {
            /* @var Job $job */
            $counter['jobs'][] = count($job->getUsers());
        }

        // Test if properties and relations can be reset
        $user
            ->setUsername(null)
            ->setFullname(null)
            ->setEmail(null)
            ->setPassword(null)
            ->setEnabled(null)
            ->setRoles([])
            ->setEndingSchoolYear(null)
        ;
        try {
            $user->setCreatedAt(null);
            $this->assertFalse(true, 'Expected resetting User::createdAt date to throw an error.');
        } catch (\Exception $exception) {
        }
        foreach ($data['jobs'] as $job) {
            $user->removeJob($job);
        }
        $this->doctrineManager->flush();

        $this->assertNull($user->getUsername());
        $this->assertNull($user->getFullname());
        $this->assertNull($user->getEmail());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertNull($user->getPassword());
        $this->assertFalse($user->isEnabled());
        $this->assertNotNull($user->getCreatedAt());
        $this->assertNull($user->getEndingSchoolYear());

        // Test job relationship
        $this->assertEquals($counter['user']['jobs'] - 1, count($user->getJobs()));
        foreach ($data['jobs'] as $index => $job) {
            /* @var Job $job */
            $this->assertEquals($counter['jobs'][$index] - 1, count($job->getUsers()));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @dataProvider propertyAccessorProvider
     */
    public function testDeleteEntity(array $data = [])
    {
        // Instantiate User
        $user = (new User())
            ->setUsername($data['username'])
            ->setFullname($data['fullname'])
            ->setEmail($data['email'])
            ->setRoles($data['roles'])
            ->setPassword($data['plainPassword'])
            ->setEnabled($data['enabled'])
        ;
        foreach ($data['jobs'] as $job) {
            // Is added two times to ensure the adder handles duplications and will add it only one time
            $user->addJob($job);
            $user->addJob($job);
            $this->doctrineManager->persist($job);
        }
        $this->doctrineManager->persist($user);
        $this->doctrineManager->flush();

        // Actual test
        $this->userManager->deleteUser($user);
        foreach ($data['jobs'] as $job) {
            /* @var Job $job */
            $this->assertFalse($job->getUsers()->contains($user), 'Expected $job instance to no longer have a reference to $user.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fluentDataProvider()
    {
        return [
            [
                [
                    'username' => 'john.doe',
                    'fullname' => 'John Doe',
                    'email' => 'john.doe@incipio.fr',
                    'roles' => 'ROLE_SUPER_ADMIN',
                    'plainPassword' => 'password',
                    'enabled' => true,
                    'jobs' => $this->getAJobInstance(),
                    'createdAt' => new \DateTime(),
                    'endingSchoolYear' => 2015,
                ],
            ],
        ];
    }

    public function propertyAccessorProvider()
    {
        return [
            [
                [
                    'username' => 'john.doe',
                    'fullname' => 'John Doe',
                    'email' => 'john.doe@incipio.fr',
                    'roles' => ['ROLE_SUPER_ADMIN'],
                    'plainPassword' => 'password',
                    'enabled' => true,
                    'jobs' => [$this->getAJobInstance()],
                    'createdAt' => new \DateTime(),
                    'endingSchoolYear' => 2015,
                ],
            ],
        ];
    }

    /**
     * @return Job
     */
    private function getAJobInstance()
    {
        $job = (new Job())
            ->setTitle('Job title')
        ;

        return $job;
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
