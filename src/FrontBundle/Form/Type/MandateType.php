<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FrontBundle\Form\Type;

use FrontBundle\Form\DataTransformer\MandateDateTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class MandateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'attr' => [
                        'placeholder' => 'Mandat 2015/2016',
                    ],
                    'label' => 'Nom :',
                    'required' => false,
                ]
            )
            ->add(
                'startAt',
                'datetime',
                [
                    'attr' => [
                        'placeholder' => '05/2015',
                    ],
                    'format' => 'MM/yyyy',
                    'input' => 'datetime',
                    'label' => 'Début de mandat :',
                    'required' => false,
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'endAt',
                'datetime',
                [
                    'attr' => [
                        'placeholder' => '06/2016',
                    ],
                    'format' => 'MM/yyyy',
                    'input' => 'datetime',
                    'label' => 'Fin de mandat :',
                    'required' => false,
                    'widget' => 'single_text',
                ]
            )
        ;

        $builder->addModelTransformer(new MandateDateTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'front_mandate';
    }
}
