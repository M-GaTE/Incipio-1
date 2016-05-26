<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiBundle\Doctrine\ORM\Filter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Dunglas\ApiBundle\Api\IriConverterInterface;
use Dunglas\ApiBundle\Api\ResourceInterface;
use Dunglas\ApiBundle\Doctrine\Orm\Filter\FilterInterface;
use Fidry\LoopBackApiBundle\Filter\FilterTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Based on Dunglas' SearchFilter, its behavior is extended to apply only to specified resources.
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
abstract class AbstractResourceSearchFilter implements FilterInterface
{
    use FilterTrait;

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        IriConverterInterface $iriConverter,
        PropertyAccessorInterface $propertyAccessor,
        array $properties = null
    ) {
        $this->iriConverter = $iriConverter;
        $this->propertyAccessor = $propertyAccessor;
        $this->properties = (null === $properties) ? $properties : array_flip($properties);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ResourceInterface $resource, QueryBuilder $queryBuilder, Request $request)
    {
        if ($this->getResourceClass() === $resource->getEntityClass()) {
            $this->applyFilter($resource, $queryBuilder, $this->extractProperties($request));
        }
    }

    /**
     * Actual logic of the filter, this function is called only if the filter is "applyable" on this resource.
     *
     * @example
     *  URI is `/api/users?filter[where][fullname][like][Admin] and $parameter is `where`:
     *  $queryValues = [
     *      'fullname' => [
     *          'like' => Admin
     *      ]
     *  ]
     *
     * @param ResourceInterface $resource
     * @param QueryBuilder      $queryBuilder
     * @param array             $queryValues
     */
    abstract protected function applyFilter(
        ResourceInterface $resource,
        QueryBuilder $queryBuilder,
        array $queryValues
    );

    /**
     * @return string FQCN for which the filter is applied.
     */
    abstract protected function getResourceClass();

    /**
     * Gets the ID from an URI or a raw ID.
     *
     * @param string $value
     *
     * @return string
     *
     * TODO: remove this function, it's an ugly copy/paste and should be refactored either at DunglasApiBundle or LoopBackApiBundle.
     */
    protected function getFilterValueFromUrl($value)
    {
        try {
            if ($item = $this->iriConverter->getItemFromIri($value)) {
                return $this->propertyAccessor->getValue($item, 'id');
            }
        } catch (\InvalidArgumentException $e) {
            // Do nothing, return the raw value
        }

        return $value;
    }
}
