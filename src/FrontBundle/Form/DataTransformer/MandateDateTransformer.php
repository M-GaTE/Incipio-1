<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Form\DataTransformer;

use Dunglas\ApiBundle\JsonLd\Serializer\DateTimeNormalizer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * DateTransformer to handle mandate dates which are passed as strings but used at datetimes.
 *
 * It was also possible to pass by the input string for the form type to avoid passing by DateTime entities. However,
 * this would imply to handle the different formats used, which is far more difficult.
 *
 * @link   http://symfony.com/doc/current/cookbook/form/data_transformers.html#about-model-and-view-transformers
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MandateDateTransformer implements DataTransformerInterface
{
    /**
     * @var NormalizerInterface|DenormalizerInterface Normalizer used by the API to normalizer datetime properties.
     */
    private $datetimeNormalizer;

    private $properties = [
        'endAt',
        'startAt',
    ];

    public function __construct()
    {
        $this->datetimeNormalizer = new DateTimeNormalizer();
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        foreach ($this->properties as $property) {
            if (isset($value[$property])) {
                $value[$property] = $this->datetimeNormalizer->denormalize($value[$property], 'DateTime', 'jsonld');
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        //TODO: works without but breaks the bijectivity of the data transformer

        return $value;
    }
}
