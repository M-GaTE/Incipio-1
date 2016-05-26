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
use ApiBundle\Manager\JobManager;

/**
 * @coversDefaultClass ApiBundle\Manager\JobManager
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class JobManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        new JobManager();
        $this->assertTrue(true);
    }

    /**
     * @covers ::remove
     * @dataProvider jobProvider
     */
    public function testDelete(Job $job)
    {
        $jobManager = new JobManager();
        $jobBefore = clone $job;

        $jobManager->remove($job);

        $this->assertEquals($jobBefore, $job);
    }

    /**
     * @covers ::update
     * @dataProvider jobProvider
     */
    public function testUpdate(Job $job, $expected)
    {
        $jobManager = new JobManager();
        $jobBefore = clone $job;

        $jobManager->update($job);

        $this->assertEquals($expected, $job->getAbbreviation());

        $job->setAbbreviation($jobBefore->getAbbreviation());
        $this->assertEquals(
            $jobBefore,
            $job,
            'Expected JobManager::updateAbbreviation() to only update Job#abbreviation'
        );
    }

    /**
     * @covers ::supports
     */
    public function testSupports()
    {
        $jobManager = new JobManager();

        $this->assertTrue($jobManager->supports(new Job()));
        $this->assertTrue($jobManager->supports(Job::class));

        $this->assertFalse($jobManager->supports('Dummy'));
        $this->assertFalse($jobManager->supports(get_class(new \stdClass())));
    }

    /**
     * @covers ::updateAbbreviation
     * @dataProvider jobProvider
     */
    public function testUpdateAbbreviation(Job $job, $expected)
    {
        $jobManager = new JobManager();
        $jobBefore = clone $job;

        $jobManager->updateAbbreviation($job);

        $this->assertEquals($expected, $job->getAbbreviation());

        $job->setAbbreviation($jobBefore->getAbbreviation());
        $this->assertEquals(
            $jobBefore,
            $job,
            'Expected JobManager::updateAbbreviation() to only update Job#abbreviation'
        );
    }

    public function jobProvider()
    {
        return [
            [
                (new Job())
                    ->setAbbreviation('Abbreviation')
                ,
                'Abbreviation',
            ],
            [
                (new Job())
                    ->setTitle('President')
                ,
                'Pres',
            ],
            [
                (new Job())
                    ->setTitle('Data Analysist')
                ,
                'DA',
            ],
            [
                (new Job())
                    ->setTitle('')
                ,
                null,
            ],
            [
                new Job(),
                null,
            ],
        ];
    }
}
