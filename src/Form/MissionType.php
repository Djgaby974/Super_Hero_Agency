<?php

namespace App\Form;

use App\Entity\Mission;
use App\Entity\Team;
use App\Entity\Power;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', null, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('startAt', null, [
                'widget' => 'single_text',
                'label' => 'Début',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('endAt', null, [
                'widget' => 'single_text',
                'label' => 'Fin',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('location', null, [
                'label' => 'Lieu',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dangerLevel', null, [
                'label' => 'Niveau de Danger',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('assignedTeam', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'label' => 'Équipe Assignée',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('requiredPowers', EntityType::class, [
                'class' => Power::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false, // Pour utiliser Choices.js
                'label' => 'Pouvoirs Requis',
                'attr' => [
                    'class' => 'js-choices', // Classe pour l'initialisation Choices.js
                ],
            ])
            ->add('isSuccessful', CheckboxType::class, [
                'label' => 'Mission Réussie',
                'required' => false,
                'attr' => [
                    'class' => 'js-switch-toggle', // Classe pour l'initialisation Switchery
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
