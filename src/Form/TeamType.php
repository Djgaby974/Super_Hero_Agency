<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\SuperHero;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Team Name',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('leader', EntityType::class, [
                'class' => SuperHero::class,
                'choice_label' => 'name',
                'label' => 'Leader',
                'query_builder' => function ($repo) {
                    return $repo->createQueryBuilder('s')
                                ->where('s.energyLevel > :energyLevel')
                                ->setParameter('energyLevel', 80);
                },
                'attr' => ['class' => 'js-choices'], // Utilisation de Choices.js
            ])
            ->add('members', EntityType::class, [
                'class' => SuperHero::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false, // Utilisation de Choices.js pour un champ multi-sélection
                'label' => 'Members',
                'query_builder' => function ($repo) {
                    return $repo->createQueryBuilder('s')
                                ->leftJoin('s.teams', 't')
                                ->where('t.id IS NULL'); // Exclure les héros déjà dans une équipe
                },
                'attr' => ['class' => 'js-choices'], // Utilisation de Choices.js
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Équipe active ?',
                'required' => false,
                'attr' => ['class' => 'js-switch-toggle'], // Utilisation de Switchery
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
