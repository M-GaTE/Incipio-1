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

use FrontBundle\Utils\IriHelper;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @link http://symfony.com/doc/current/cookbook/form/data_transformers.html#about-model-and-view-transformers
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class StudentConventionTransformer implements DataTransformerInterface
{
    /**
     * API URI for the StudentConvention. Is put as a constant here to avoid to have to inject the router service to
     * generate it. This would indeed result in declaring those forms as services which is quite heavy and ugly.
     */
    const API_URI_PREFIX = '/api/student_conventions';

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return;
        }

        if (isset($value['@id'])) {
            $value['@id'] = IriHelper::extractId($value['@id']);
        }
        if (isset($value['dateOfSignature'])) {
            $value['dateOfSignature'] = (true === empty($value['dateOfSignature']))
                ? null
                : new \DateTime($value['dateOfSignature'])
            ;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return;
        }

        if (isset($value['@id']) && false === empty($value['@id'])) {
            $value['@id'] = sprintf('%s/%s', self::API_URI_PREFIX, IriHelper::extractId($value['@id']));
        }
        if (isset($value['dateOfSignature']) && $value['dateOfSignature'] instanceof \DateTime) {
            $value['dateOfSignature'] = $value['dateOfSignature']->format('Y-m-d\TH:i:sP');
        }

        return $value;
    }
}
