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
use PHPUnit\Framework\AbstractEntityTestCase;

/**
 * @coversDefaultClass ApiBundle\Entity\Mandate
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class MandateTest extends AbstractEntityTestCase
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClassName()
    {
        return Mandate::class;
    }

    /**
     * {@inheritdoc}
     *
     * @covers ::getId
     * @covers ::setStartAt
     * @covers ::getStartAt
     * @covers ::setName
     * @covers ::getName
     * @covers ::setEndAt
     * @covers ::getEndAt
     * @covers ::addJob
     * @covers ::removeJob
     * @covers ::getJobs
     * @dataProvider propertyAccessorProvider
     */
    public function testPropertyAccessors(array $data = [])
    {
        $mandate = (new Mandate())
            ->setEndAt($data['endAt'])
            ->setName($data['name'])
            ->setStartAt($data['startAt'])
        ;
        foreach ($data['jobs'] as $job) {
            // Is added two times to ensure the adder handles duplications and will add it only one time
            $mandate->addJob($job);
            $mandate->addJob($job);
        }

        $this->doctrineManager->persist($mandate);
        $this->doctrineManager->flush();

        // Test classic setters
        $this->assertNotNull($mandate->getId());
        $this->assertEquals($data['name'], $mandate->getName());
        $this->assertEquals($data['endAt']->format('Y-m-d'), $mandate->getEndAt()->format('Y-m-d'));
        $this->assertEquals($data['startAt']->format('Y-m-d'), $mandate->getStartAt()->format('Y-m-d'));

        // Test job relationship
        $this->assertEquals(count($data['jobs']), count($mandate->getJobs()));
        foreach ($data['jobs'] as $job) {
            /* @var Job $job */
            $this->assertTrue($mandate->getJobs()->contains($job));
            $this->assertEquals($mandate, $job->getMandate());
        }

        // Test if properties and relations can be reset
        $mandate
            ->setName(null)
            ->setEndAt(null)
        ;
        foreach ($data['jobs'] as $job) {
            $mandate->removeJob($job);
        }
        $this->doctrineManager->flush();

        $this->assertNull($mandate->getEndAt());
        $this->assertNotNull($mandate->getName(), 'Name should not have be reset as is determined by ::startAt');

        // Test job relationship
        $this->assertEquals(0, count($mandate->getJobs()));
        foreach ($data['jobs'] as $job) {
            /* @var Job $job */
            $this->assertNull($job->getMandate());
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
        $mandate = (new Mandate())
            ->setEndAt($data['endAt'])
            ->setName($data['name'])
            ->setStartAt($data['startAt'])
        ;
        foreach ($data['jobs'] as $job) {
            // Is added two times to ensure the adder handles duplications and will add it only one time
            $mandate->addJob($job);
            $mandate->addJob($job);
        }
        $this->doctrineManager->persist($mandate);
        $this->doctrineManager->flush();

        // Actual test
        $this->doctrineManager->remove($mandate);
        $this->doctrineManager->flush();
        foreach ($data['jobs'] as $job) {
            /* @var Job $job */
            $this->assertNull($job->getMandate(), 'Expected $job instance to no longer have a reference to the mandate.');
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
                    'endAt' => new \DateTime('2016-03-02'),
                    'jobs' => $this->getAJobInstance(),
                    'name' => 'Dummy Mandate',
                    'startAt' => new \DateTime('2015-03-02'),
                ],
            ],
        ];
    }

    public function propertyAccessorProvider()
    {
        return [
            [
                [
                    'endAt' => new \DateTime('2016-03-02'),
                    'jobs' => [$this->getAJobInstance()],
                    'name' => 'Dummy Mandate',
                    'startAt' => new \DateTime('2015-03-02'),
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
