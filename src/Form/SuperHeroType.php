<?php

namespace App\Form;

use App\Entity\SuperHero;
use App\Entity\Power;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SuperHeroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('alterEgo', TextType::class, [
                'label' => 'Alter Ego',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isAvailable', CheckboxType::class, [
                'label' => 'Available?',
                'required' => false,
            ])
            ->add('energyLevel', IntegerType::class, [
                'label' => 'Energy Level',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 100,
                ],
            ])
            ->add('biography', TextareaType::class, [
                'label' => 'Biography',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Hero Image (JPEG or PNG only)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG or PNG).',
                    ]),
                ],
            ])
            ->add('createdAt', null, [
                'label' => 'Created At',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('powers', EntityType::class, [
                'class' => Power::class,
                'choice_label' => 'name',
                'label' => 'Powers',
                'multiple' => true, // Permet de sélectionner plusieurs pouvoirs
                'expanded' => true, // Affiche des cases à cocher
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SuperHero::class,
        ]);
    }
}
