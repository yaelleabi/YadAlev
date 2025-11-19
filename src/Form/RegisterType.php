<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;


class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
             
            ->add('name', null, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre nom',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre nom.',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'max' => 50,
                    ]),
                ],
                'label' => 'Nom',
            ])


            ->add('email', null, [
            'attr' => [
                'class' => 'form-control', 
                'placeholder' => 'Entrez votre adresse email',
                
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrer une adresse email.',
                ]),
                new Email([
                    'message' => 'L\'adresse email {{ value }} n\'est pas une adresse valide.',
                ]),
            ],
            'label' => 'Adresse mail'
        ])
           ->add('phoneNumber', null, [
            'required' => true,
            'attr' => [
            'class' => 'form-control',
            'placeholder' => 'Entrez votre numéro de téléphone',
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrer un numéro de téléphone.',
                ]),
                new Regex([
                    'pattern' => '/^\+?\d{10,15}$/',
                    'message' => 'Veuillez entrer un numéro de téléphone valide (10 à 15 chiffres, peut commencer par +).',
                ]),
            ],
            'label' => 'Téléphone',
        ])
           ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Famille' => 'ROLE_FAMILY',
                    'Volontaire' => 'ROLE_VOLUNTEER',
                ],
                'expanded' => true, // Afficher sous forme de cases à cocher
                'multiple' => false, // Une seule option sélectionnable
                
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options'  => [
                    'label' => 'Mot de passe',
                    'attr' => ['placeholder' => 'Entrez un mot de passe']
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                    'attr' => ['placeholder' => 'Confirmez votre mot de passe']
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                ],
            ])
           
        ;
    
    
    $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // Transforme un tableau en chaîne pour l'affichage dans le formulaire
                    return is_array($rolesArray) && count($rolesArray) > 0 ? $rolesArray[0] : null;
                },
                function ($rolesString) {
                    // Transforme une chaîne en tableau pour la sauvegarde dans l'entité
                    return [$rolesString];
                }
            ));
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
