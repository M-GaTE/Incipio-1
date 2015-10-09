<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Tests\Doctrine\ORM\Filter;

use ApiBundle\Doctrine\ORM\Filter\User\UserTypeFilter;
use Doctrine\Common\Persistence\ManagerRegistry;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @coversDefaultClass ApiBundle\Doctrine\ORM\Filter\UserTypeFilter
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class UserTypeFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $managerRegistry = $this->prophesize(ManagerRegistry::class)->reveal();
        $iriConverter = $this->prophesize(IriConverterInterface::class)->reveal();
        $propertyAccessor = $this->prophesize(PropertyAccessorInterface::class)->reveal();

        new UserTypeFilter($managerRegistry, $iriConverter, $propertyAccessor);
        new UserTypeFilter($managerRegistry, $iriConverter, $propertyAccessor, null);
        new UserTypeFilter($managerRegistry, $iriConverter, $propertyAccessor, []);

        $this->assertTrue(true);
    }

    /**
     * @dataProvider queryProvider
     *
     * @param string $uri
     * @param string $expectedDQL
     * @param array  $expectedParameters
     */
    public function testApply($uri, $expectedDQL, array $expectedParameters)
    {
        $managerRegistry = $this->prophesize(ManagerRegistry::class)->reveal();
        $iriConverter = $this->prophesize(IriConverterInterface::class)->reveal();
        $propertyAccessor = $this->prophesize(PropertyAccessorInterface::class)->reveal();
        $request = Request::create($uri, 'GET');

        $filter = new UserTypeFilter($managerRegistry, $iriConverter, $propertyAccessor);
        $filter->initParameter('where');
        $resource = $this->prophesize(ResourceInterface::class);
        $resource->getEntityClass()->willReturn('ApiBundle\Entity\Dummy');
        $queryBuilder = $this->getQueryBuilder();

        $filter->apply($resource->reveal(), $queryBuilder, $request);

        $actualDQL = strtolower($queryBuilder->getQuery()->getDQL());
        $this->assertEquals(strtolower('SELECT o FROM ApiBundle\Entity\User o'), $actualDQL);
        $this->assertEquals(0, count($queryBuilder->getParameters()));

        $filter = new UserTypeFilter($managerRegistry, $iriConverter, $propertyAccessor);

        $filter->initParameter('where');
        $resource = $this->prophesize(ResourceInterface::class);
        $resource->getEntityClass()->willReturn('ApiBundle\Entity\User');
        $queryBuilder = $this->getQueryBuilder();

        $filter->apply($resource->reveal(), $queryBuilder, $request);

        $actualDQL = strtolower($queryBuilder->getQuery()->getDQL());
        $expectedDQL = strtolower(sprintf('SELECT o FROM ApiBundle\Entity\User o%s', ('' === $expectedDQL) ? $expectedDQL : " $expectedDQL"));

        $this->assertEquals($expectedDQL, $actualDQL);
        $this->assertEquals(count($expectedParameters), count($queryBuilder->getParameters()));
        foreach ($expectedParameters as $parameter => $value) {
            $this->assertEquals($value, $queryBuilder->getParameter($parameter)->getValue());
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder QueryBuilder for filters.
     */
    public function getQueryBuilder()
    {
        return DoctrineTestHelper::createTestEntityManager()
            ->getRepository('ApiBundle\Entity\User')
            ->createQueryBuilder('o')
        ;
    }

    public function queryProvider()
    {
        return [
            [
                '/api/dummies?filter[where][type]=TYPE_CONTRACTOR',
                'WHERE o.types LIKE :user_type',
                [
                    'user_type' => '%TYPE_CONTRACTOR%',
                ],
            ],
            [
                '/api/dummies?filter[where][type]=contractor',
                'WHERE o.types LIKE :user_type',
                [
                    'user_type' => '%TYPE_CONTRACTOR%',
                ],
            ],
            [
                '/api/dummies?filter[where][type]=TYPE_MEMBER',
                'WHERE o.types LIKE :user_type',
                [
                    'user_type' => '%TYPE_MEMBER%',
                ],
            ],
            [
                '/api/dummies?filter[where][type]=member',
                'WHERE o.types LIKE :user_type',
                [
                    'user_type' => '%TYPE_MEMBER%',
                ],
            ],
            [
                '/api/dummies?filter[where][type][]=member',
                '',
                [],
            ],
            [
                '/api/dummies?filter[where][type]=dummy',
                '',
                [],
            ],
            [
                '/api/dummies?filter[where][type]=TYPE_DUMMY',
                '',
                [],
            ],
            [
                '/api/dummies?filter[where][dummy]=member',
                '',
                [],
            ],
        ];
    }
}
