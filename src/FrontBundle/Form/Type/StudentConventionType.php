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

use FrontBundle\Form\DataTransformer\StudentConventionTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class StudentConventionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'reference',
                'text',
                [
                    'attr' => [
                        'placeholder' => 'Référence',
                    ],
                    'disabled' => true,
                    'label' => 'Référence :',
                    'property_path' => '[@id]',
                ])
            ->add(
                'dateOfSignature',
                'date',
                [
                    'label' => 'Date de signature :',
                    'required' => false,
                    'widget' => 'single_text',
                ]
            )
        ;

        $builder->addModelTransformer(new StudentConventionTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'front_student_convention';
    }
}
