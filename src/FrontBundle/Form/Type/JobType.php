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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class JobType extends AbstractType
{
    /**
     * @var array
     */
    private $mandates;

    /**
     * @param array $mandates Array where keys are Mandate IRI and values are their matching name.
     */
    public function __construct(array $mandates)
    {
        $this->mandates = $mandates;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                'text',
                [
                    'attr' => [
                        'placeholder' => 'Nom du poste',
                    ],
                    'label' => 'Nom :',
                ])
            ->add(
                'abbreviation',
                'text',
                [
                    'attr' => [
                        'placeholder' => 'Alias',
                    ],
                    'label' => 'Alias :',
                    'required' => false,
                ])
            ->add(
                'mandate_id',
                'choice',
                [
                    'choices' => $this->mandates,
                    'empty_data' => null,
                    'empty_value' => 'Aucun',
                    'label' => 'Mandat :',
                    'required' => false,
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'front_user';
    }
}
