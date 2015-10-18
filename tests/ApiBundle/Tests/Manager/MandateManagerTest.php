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
use ApiBundle\Entity\Mandate;
use ApiBundle\Manager\MandateManager;
use Prophecy\Argument;

/**
 * @coversDefaultClass ApiBundle\Manager\MandateManager
 *
 * @author             Théo FIDRY <theo.fidry@gmail.com>
 */
class MandateManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        new MandateManager();
        $this->assertTrue(true);
    }

    /**
     * @covers ::remove
     */
    public function testDelete()
    {
        $mandateManager = new MandateManager();

        $mandateWithJobs = new Mandate();

        $firstJobProphecy = $this->prophesize(Job::class);
        $firstJobProphecy->setMandate($mandateWithJobs)->shouldBeCalledTimes(1);
        $firstJobProphecy->setMandate(null)->shouldBeCalledTimes(1);
        $secondJobProphecy = $this->prophesize(Job::class);
        $secondJobProphecy->setMandate($mandateWithJobs)->shouldBeCalledTimes(1);
        $secondJobProphecy->setMandate(null)->shouldBeCalledTimes(1);

        $mandateWithJobs = $mandateWithJobs
            ->addJob($firstJobProphecy->reveal())
            ->addJob($secondJobProphecy->reveal())
        ;

        $mandateWithoutJobs = new Mandate();

        $mandateManager->remove($mandateWithJobs);
        $mandateManager->remove($mandateWithoutJobs);
    }

    /**
     * @covers ::update
     * @dataProvider mandateProvider
     */
    public function testUpdate(Mandate $mandate, $expected)
    {
        $mandateManager = new MandateManager();
        $mandateBefore = clone $mandate;

        $mandateManager->update($mandate);

        $this->assertEquals(
            $expected['value'],
            $mandate->getName(),
            $expected['message']
        );

        $mandate->setName($mandateBefore->getName());
        $this->assertEquals(
            $mandateBefore,
            $mandate,
            'Expected MandateManager::updateName() to only update Job#abbreviation'
        );
    }

    /**
     * @covers ::update
     */
    public function testUpdateWithNonEmptyName()
    {
        $mandateProphecy = $this->prophesize(Mandate::class);
        $mandateProphecy->getName()->willReturn('Non empty name');
        $mandateProphecy->setName(Argument::any())->shouldNotBeCalled();

        $mandateManager = new MandateManager();

        $mandateManager->update($mandateProphecy->reveal());
    }

    /**
     * @covers ::supports
     */
    public function testSupports()
    {
        $mandateManager = new MandateManager();

        $this->assertTrue($mandateManager->supports(new Mandate()));
        $this->assertTrue($mandateManager->supports(Mandate::class));

        $this->assertFalse($mandateManager->supports('Dummy'));
        $this->assertFalse($mandateManager->supports(get_class(new \stdClass())));
    }

    /**
     * @covers ::updateName
     * @dataProvider mandateProvider
     */
    public function testUpdateName(Mandate $mandate, $expected)
    {
        $mandateManager = new MandateManager();
        $mandateBefore = clone $mandate;

        $mandateManager->updateName($mandate);

        $this->assertEquals(
            $expected['value'],
            $mandate->getName(),
            $expected['message']
        );

        if (false === empty($mandateBefore->getName())) {
            $this->assertEquals(
                $mandateBefore->getName(),
                $mandate->getName(),
                'Did not expected mandate name to change if already set.'
            );
        }

        $mandate->setName($mandateBefore->getName());
        $this->assertEquals(
            $mandateBefore,
            $mandate,
            'Expected MandateManager::updateName() to only update Mandate#name'
        );
    }

    public function mandateProvider()
    {
        return [
            // Mandates without names
            [
                (new Mandate())
                    ->setStartAt(
                        (new \DateTime())
                            ->setDate(2000, 01, 01)
                    )
                    ->setEndAt(
                        (new \DateTime())
                            ->setDate(2001, 01, 01)
                    )
                ,
                [
                    'value' => 'Mandate 2000/2001',
                    'message' => 'Expected a name with the mask \'Mandate startYear/endYear\'',
                ],
            ],
            [
                (new Mandate())
                    ->setStartAt(
                        (new \DateTime())
                            ->setDate(2000, 01, 01)
                    )
                    ->setEndAt(
                        (new \DateTime())
                            ->setDate(2000, 05, 01)
                    )
                ,
                [
                    'value' => 'Mandate 01 2000',
                    'message' => 'Expected a name with the mask \'Mandate startMonth Year\'',
                ],
            ],

            // Mandates with empty names
            [
                (new Mandate())
                    ->setStartAt(
                        (new \DateTime())
                            ->setDate(2000, 01, 01)
                    )
                    ->setEndAt(
                        (new \DateTime())
                            ->setDate(2001, 01, 01)
                    )
                    ->setName('')
                ,
                [
                    'value' => 'Mandate 2000/2001',
                    'message' => 'Expected a name with the mask \'Mandate startYear/endYear\'',
                ],
            ],
            [
                (new Mandate())
                    ->setStartAt(
                        (new \DateTime())
                            ->setDate(2000, 01, 01)
                    )
                    ->setEndAt(
                        (new \DateTime())
                            ->setDate(2000, 05, 01)
                    )
                    ->setName('')
                ,
                [
                    'value' => 'Mandate 01 2000',
                    'message' => 'Expected a name with the mask \'Mandate startMonth Year\'',
                ],
            ],

            // Mandates with names
            [
                (new Mandate())
                    ->setStartAt(
                        (new \DateTime())
                            ->setDate(2000, 01, 01)
                    )
                    ->setEndAt(
                        (new \DateTime())
                            ->setDate(2001, 01, 01)
                    )
                    ->setName('Mandate 2000/2001')
                ,
                [
                    'value' => 'Mandate 2000/2001',
                    'message' => 'Expected a name with the mask \'Mandate startYear/endYear\'',
                ],
            ],
            [
                (new Mandate())
                    ->setStartAt(
                        (new \DateTime())
                            ->setDate(2000, 01, 01)
                    )
                    ->setEndAt(
                        (new \DateTime())
                            ->setDate(2000, 05, 01)
                    )
                    ->setName('Another dummy mandate')
                ,
                [
                    'value' => 'Another dummy mandate',
                    'message' => 'Expected a name with the mask \'Mandate startMonth Year\'',
                ],
            ],

            // Empty mandate
            [
                new Mandate()
                ,
                [
                    'value' => null,
                    'message' => 'Expected mandate not to have a name',
                ],
            ],
        ];
    }
}
