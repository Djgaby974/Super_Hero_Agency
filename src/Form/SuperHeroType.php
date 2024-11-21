<?php

namespace App\Form;

use App\Entity\SuperHero;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SuperHeroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('alterEgo')
            ->add('isAvailable')
            ->add('energyLevel')
            ->add('biography')
            ->add('image', FileType::class, [
                'label' => 'Hero Image (JPEG or PNG only)',
                'mapped' => false, // Ce champ n'est pas lié directement à l'entité
                'required' => false, // L'image n'est pas obligatoire
                'constraints' => [
                    new File([
                        'maxSize' => '2M', // Taille maximale : 2 Mo
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG or PNG).',
                    ]),
                ],
            ])
            ->add('imageName', null, [
                'required' => false,
                'disabled' => true, // On rend ce champ non modifiable via le formulaire
            ])
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SuperHero::class,
        ]);
    }
}
