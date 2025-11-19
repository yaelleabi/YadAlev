<?php

namespace App\Form;

use App\Entity\Adress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AdressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('streetNumber', TextType::class, [
                'label' => 'Numéro de rue',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]+$/',
                        'message' => 'Le numéro de rue doit contenir uniquement des chiffres.',
                    ]),
                ],
            ])
            ->add('street', TextType::class, [
                'label' => 'Rue',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^[A-Za-zÀ-ÿ0-9\s\-]+$/u',
                        'message' => 'La rue ne doit contenir que des lettres, chiffres et espaces.',
                    ]),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'attr' => ['class' => 'form-control', 'maxlength' => 5],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]{5}$/',
                        'message' => 'Le code postal doit comporter exactement 5 chiffres.',
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr' => [
                    'readonly' => true,
                    'class' => 'form-control bg-light'
                ],
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La ville est obligatoire.'
                    ]),
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adress::class,
        ]);
    }
}
