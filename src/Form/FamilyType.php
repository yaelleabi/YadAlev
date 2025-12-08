<?php

namespace App\Form;

use App\Entity\Family;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\AdressType;
use Symfony\Component\Validator\Constraints as Assert;

class FamilyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

        /** ========== 1 — IDENTITE ========== **/
        ->add('name', TextType::class, [
            'label' => 'Nom',
            'required' => true,
            'disabled' => true,
            'attr' => ['maxlength' => 100],
        ])

        ->add('firstName', TextType::class, [
            'label' => 'Prénom',
            'required' => true,
            'attr' => ['maxlength' => 100],
        ])

        ->add('dateOfBirth', DateType::class, [
            'label' => 'Date de naissance',
            'required' => true,
            'widget' => 'single_text',
        ])

        ->add('email', EmailType::class, [
            'label' => 'Adresse e-mail',
            'disabled' => true,
            'required' => true,
        ])

        ->add('phoneNumber', TextType::class, [
            'label' => 'Numéro de téléphone',
            'required' => true,
        ])


        /** ========== 2 — ADRESSE ========== **/
        ->add('adress', AdressType::class, ['label' => 'Adresse'])


        /** ========== 3 — SITUATION FAMILIALE & LOGEMENT ========== **/
        ->add('maritalStatus', ChoiceType::class, [
            'label' => 'Situation familiale',
            'choices' => [
                'Célibataire' => 'Célibataire',
                'Marié(e)' => 'Marié(e)',
                'Divorcé(e)' => 'Divorcé(e)',
                'Veuf(ve)' => 'Veuf(ve)',
                'Séparé(e)' => 'Séparé(e)',
            ],
            'placeholder' => 'Sélectionnez votre situation familiale',
        ])

        ->add('dependantsCount', NumberType::class, [
            'label' => 'Enfants à charge',
            'required' => false,
        ])

        ->add('housingStatus', ChoiceType::class, [
            'label' => 'Situation de logement',
            'choices' => [
                'Locataire' => 'Locataire',
                'Propriétaire' => 'Propriétaire',
                'Hébergé(e)' => 'Hébergé(e)',
                'Autre' => 'Autre',
            ],
            'placeholder' => 'Sélectionnez votre situation de logement',
        ])


        /** ========== 4 — FINANCES ========== **/
        ->add('employmentStatus', ChoiceType::class, [
            'label' => 'Situation professionnelle',
            'choices' => [
                'CDI' => 'CDI',
                'CDD' => 'CDD',
                'Intérim' => 'Intérim',
                'Auto-entrepreneur' => 'Auto-entrepreneur',
                'Chômage' => 'Chômage',
                'RSA' => 'RSA',
                'Autre' => 'Autre',
            ],
            'placeholder' => 'Sélectionnez votre situation professionnelle',
        ])

        ->add('monthlyIncome', NumberType::class, [
            'label' => 'Revenu mensuel (€)',
            'required' => true
        ])

        ->add('spouseEmploymentStatus', ChoiceType::class, [
            'label' => 'Statut professionnel du conjoint',
            'choices' => [
                'CDI' => 'CDI',
                'CDD' => 'CDD',
                'Intérim' => 'Intérim',
                'Auto-entrepreneur' => 'Auto-entrepreneur',
                'Chômage' => 'Chômage',
                'RSA' => 'RSA',
                'Autre' => 'Autre',
            ],
            'required' => false,
            'placeholder' => 'Sélectionnez (si applicable)',
        ])

        ->add('spouseMonthlyIncome', NumberType::class, [
            'label' => 'Revenus du conjoint (€)',
            'required' => false,
        ])

        ->add('familyAllowanceAmount', NumberType::class, [
            'label' => 'Allocations familiales (€)',
            'required' => false
        ])

        ->add('alimonyAmount', NumberType::class, [
            'label' => 'Pension alimentaire (€)',
            'required' => false
        ])

        ->add('rentAmountNetAide', NumberType::class, [
            'label' => 'Loyer net après aide (€)',
            'required' => false
        ])


        /** ========== 5 — AUTRES ========== **/
        ->add('otherNeed', TextareaType::class, [
            'label' => 'Autres besoins éventuels',
            'required' => false,
        ])

        ->add('otherComment', TextareaType::class, [
            'label' => 'Informations complémentaires',
            'required' => false,
        ])


        /** ========== 6 — DOCUMENTS ========== **/
        ->add('identityProofFilename', FileType::class, [
            'label' => 'Justificatif d’identité',
            'mapped' => false,
            'required' => false
        ])
        ->add('incomeProofFilename', FileType::class, [
            'label' => 'Justificatif de revenus',
            'mapped' => false,
            'required' => false
        ])
        ->add('taxNoticeFilename', FileType::class, [
            'label' => 'Avis d’imposition',
            'mapped' => false,
            'required' => false
        ])
        ->add('quittanceLoyer', FileType::class, [
            'label' => 'Quittance de loyer',
            'mapped' => false,
            'required' => false
        ])
        ->add('avisCharge', FileType::class, [
            'label' => 'Avis de charges',
            'mapped' => false,
            'required' => false
        ])
        ->add('taxeFonciere', FileType::class, [
            'label' => 'Taxe foncière',
            'mapped' => false,
            'required' => false
        ])
        ->add('fraisScolarite', FileType::class, [
            'label' => 'Frais de scolarité',
            'mapped' => false,
            'required' => false
        ])
        ->add('attestationCaf', FileType::class, [
            'label' => 'Attestation CAF',
            'mapped' => false,
            'required' => false
        ])
        ->add('otherDocumentFilename', FileType::class, [
            'label' => 'Autre document',
            'mapped' => false,
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Family::class,
        ]);
    }
}
