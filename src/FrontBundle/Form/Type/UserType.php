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

use FrontBundle\Utils\IriHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $types = (isset($options['data']['types'])) ? $options['data']['types'] : [];

        $builder
            ->add(
                'fullname',
                'text',
                [
                    'attr' => [
                        'placeholder' => 'Prénom NOM',
                    ],
                    'label' => 'Nom complet :',
                ])
            ->add(
                'username',
                'text',
                [
                    'attr' => [
                        'placeholder' => 'prenom.nom',
                    ],
                    'label' => 'Nom d\'utilisateur :',
                    'required' => false,
                ])
            ->add(
                'email',
                'email',
                [
                    'attr' => [
                        'placeholder' => 'email@example.com',
                    ],
                    'label' => 'Email :',
                ])
            ->add(
                'organizationEmail',
                'email',
                [
                    'attr' => [
                        'placeholder' => 'email@example.com',
                    ],
                    'label' => 'Email professionnel :',
                    'required' => false,
                ])
            ->add(
                'endingSchoolYear',
                'integer',
                [
                    'attr' => [
                        'placeholder' => 2015,
                    ],
                    'label' => 'Promotion :',
                    'required' => false,
                    'scale' => 0,
                ])
            ->add(
                'user_type',
                'choice',
                [
                    'choices' => [
                        'TYPE_MEMBER' => 'Membre',
                        'TYPE_CONTRACTOR' => 'Intervenant',
                    ],
                    'label' => 'Type(s) :',
                    'expanded' => true,
                    'multiple' => true,
                    'data' => $types,
                ]
            )
            ->add(
                'enabled',
                'checkbox',
                [
                    'data' => false,
                    'label' => 'Activé',
                    'required' => false,
                ]
            )
            ->add(
                'studentConvention',
                new StudentConventionType(),
                [
                    'label' => 'Convention étudiante :',
                ]
            )
            // job
        ;

//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            [$this, 'onPreSetData']
//        );
    }

    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();

        $data['@id'] = IriHelper::extractId($data['@id']);

        if (isset($data['studentConvention']['@id'])) {
            $data['studentConvention']['@id'] = IriHelper::extractId($data['studentConvention']['@id']);
        }

        $event->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'front_user';
    }
}
