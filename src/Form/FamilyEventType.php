<?php

namespace App\Form;

use App\Entity\FamilyEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FamilyEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Title', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Veuillez entrer un titre',
                    ]),
                ],
            ])
            ->add('Description', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Veuillez entrer une description',
                    ]),
                ],
            ])
            ->add('Quantity', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'Quantité liée',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Veuillez définir une quantité (même 0)',
                    ]),
                ],
            ])
            ->add('startDate', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'La date de début est requise',
                    ]),
                ],
            ])
            ->add('endDate', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'La date de fin est requise',
                    ]),
                ],
            ])
            ->add('isVisible', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label' => 'Visible',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('assignedFamilies', null, [
                'label' => 'Familles assignées',
                'expanded' => true,
                'multiple' => true,
                'attr' => ['class' => 'family-checkbox-list'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FamilyEvent::class,
        ]);
    }
}
