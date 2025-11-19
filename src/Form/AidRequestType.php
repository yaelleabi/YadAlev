<?php 

namespace App\Form;

use App\Entity\AidRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use App\Form\AdressType;

class AidRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isFamily = $options['is_family'];

        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'disabled' => $isFamily,
                'attr' => ['class' => 'form-control', 'maxlength' => 100],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Assert\Regex([
                        'pattern' => '/^[A-Za-zÀ-ÿ\s\-]+$/u',
                        'message' => 'Le nom ne doit contenir que des lettres et des espaces.',
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit comporter au moins {{ limit }} caractères.',
                        'max' => 100,
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'data' => null, // 🔥 vide toujours le champ, même si l'entité a une valeur
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 100,
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le prénom est obligatoire.'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Za-zÀ-ÿ\s\-]+$/u',
                        'message' => 'Le prénom ne doit contenir que des lettres et des espaces.',
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'Le prénom doit comporter au moins {{ limit }} caractères.',
                        'max' => 100,
                    ]),
                ],
            ])
            ->add('dateOfBirth', DateType::class, [
                'label' => 'Date de naissance',
                'required' => true,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de naissance est obligatoire.']),
                    new Assert\LessThan([
                        'value' => 'today',
                        'message' => 'La date de naissance doit être antérieure à aujourd’hui.',
                    ]),
                    new Assert\LessThan([
                        'value' => (new \DateTime())->modify('-18 years'),
                        'message' => 'Vous devez avoir au moins 18 ans.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'required' => true,
                'disabled' => $isFamily,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L’adresse e-mail est obligatoire.']),
                    new Assert\Email(['message' => 'Veuillez saisir une adresse e-mail valide.']),
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone',
                'required' => true,
                'disabled' => $isFamily,
                'attr' => ['class' => 'form-control', 'maxlength' => 20],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de téléphone est obligatoire.']),
                    new Assert\Regex([
                        'pattern' => '/^\+?\d{9,15}$/',
                        'message' => 'Le numéro de téléphone doit comporter entre 9 et 15 chiffres.',
                    ]),
                ],
            ])

            ->add('adress', AdressType::class, [
                'label' => false,
            ])

            ->add('housingStatus', ChoiceType::class, [
                'label' => 'Situation de logement',
                'required' => true,
                'placeholder' => 'Sélectionnez votre situation de logement',
                'choices' => [
                    'Locataire' => 'Locataire',
                    'Propriétaire' => 'Propriétaire',
                    'Hébergé(e)' => 'Hébergé(e)',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer votre situation de logement.'])
                ],
            ])
            ->add('maritalStatus', ChoiceType::class, [
                'label' => 'Situation familiale',
                'required' => true,
                'placeholder' => 'Sélectionnez votre situation familiale',
                'choices' => [
                    'Célibataire' => 'Célibataire',
                    'Marié(e)' => 'Marié(e)',
                    'Divorcé(e)' => 'Divorcé(e)',
                    'Veuf(ve)' => 'Veuf(ve)',
                    'Séparé(e)' => 'Séparé(e)',
                ],
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer votre situation familiale.'])
                ],
            ])
            ->add('dependantsCount', NumberType::class, [
                'label' => 'Nombre d’enfants à charge',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le nombre d’enfants doit être positif.']),
                ],
            ])

            ->add('employmentStatus', ChoiceType::class, [
                'label' => 'Situation professionnelle',
                'required' => true,
                'placeholder' => 'Sélectionnez votre situation professionnelle',
                'choices' => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Intérim' => 'Intérim',
                    'Auto-entrepreneur' => 'Auto-entrepreneur',
                    'Chômage' => 'Chômage',
                    'Invalidité' => 'Invalidité',
                    'RSA' => 'RSA',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer votre situation professionnelle.'])
                ],
            ])
            ->add('monthlyIncome', NumberType::class, [
                'label' => 'Revenu mensuel (€)',
                'required' => true,
                'attr' => ['class' => 'form-control', 'min' => 0],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer votre revenu mensuel.']),
                    new Assert\PositiveOrZero(['message' => 'Le revenu doit être positif.']),
                ],
            ])
            ->add('spouseEmploymentStatus', ChoiceType::class, [
                'label' => 'Situation professionnelle du conjoint  (si applicable)',
                'required' => true,
                'placeholder' => 'Sélectionnez la situation du conjoint',
                'choices' => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Intérim' => 'Intérim',
                    'Auto-entrepreneur' => 'Auto-entrepreneur',
                    'Chômage' => 'Chômage',
                    'Invalidité' => 'Invalidité',
                    'RSA' => 'RSA',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('spouseMonthlyIncome', NumberType::class, [
                'label' => 'Revenu mensuel du conjoint (€)',
                'required' => true,
                'attr' => ['class' => 'form-control', 'min' => 0],
                'constraints' => [
                    new Assert\PositiveOrZero(),
                ],
            ])
            ->add('familyAllowanceAmount', NumberType::class, [
                'label' => 'Allocations familiales (€)',
                'required' => true,
                'attr' => ['class' => 'form-control', 'min' => 0],
                'constraints' => [
                    new Assert\PositiveOrZero(),
                ],
            ])
            ->add('alimonyAmount', NumberType::class, [
                'label' => 'Pension alimentaire (€)',
                'required' => true,
                'attr' => ['class' => 'form-control', 'min' => 0],
                'constraints' => [
                    new Assert\PositiveOrZero(),
                ],
            ])
            ->add('rentAmountNetAide', NumberType::class, [
                'label' => 'Montant du loyer (net des aides) (€) (si locataire)',
                'required' => true,
                'attr' => ['class' => 'form-control', 'min' => 0],
                'constraints' => [
                    new Assert\PositiveOrZero(),
                ],
            ])

            ->add('requestType', ChoiceType::class, [
                'label' => 'Type de demande',
                'required' => true,
                'placeholder' => 'Sélectionnez un type de demande',
                'choices' => [
                    'Bon alimentaire' => 'Bon alimentaire',
                    'Colis alimentaire' => 'Colis alimentaire',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un type de demande.'])
                ],
            ])
            ->add('requestDuration', ChoiceType::class, [
                'label' => 'Durée de la demande',
                'required' => true,
                'choices' => [
                    '1 mois' => '1 mois',
                    '3 mois' => '3 mois',
                    '6 mois' => '6 mois',
                    'Autre' => 'Autre',
                ],
                'placeholder' => 'Sélectionnez une durée',
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner une durée.'])
                ],
            ])
            ->add('otherRequestDuration', TextType::class, [
                'label' => 'Autre durée (si "Autre")',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('requestReason', TextareaType::class, [
                'label' => 'Motif de la demande',
                'required' => true,
                'attr' => ['rows' => 3, 'class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer le motif de votre demande.'])
                ],
            ])
            ->add('otherNeed', TextareaType::class, [
                'label' => 'Autre besoin éventuel',
                'required' => false,
                'attr' => ['rows' => 2, 'class' => 'form-control'],
            ])
            ->add('urgencyLevel', ChoiceType::class, [
                'label' => 'Niveau d’urgence',
                'required' => true,
                'placeholder' => 'Sélectionnez un niveau d’urgence',
                'choices' => [
                    'Faible' => 1,
                    'Moyenne' => 2,
                    'Élevée' => 3,
                ],
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer le niveau d’urgence.'])
                ],
            ])
            ->add('urgencyExplanation', TextareaType::class, [
                'label' => 'Quelle est votre situation actuelle et en quoi est-elle urgente ?',
                'required' => true,
                'attr' => ['rows' => 3, 'class' => 'form-control'],
            ])
            ->add('otherComment', TextareaType::class, [
                'label' => 'Autre commentaire',
                'required' => false,
                'attr' => ['rows' => 3, 'class' => 'form-control'],
            ])

            ->add('identityProofFilename', FileType::class, [
                'label' => 'Justificatif d’identité',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('incomeProofFilename', FileType::class, [
                'label' => 'Justificatif de revenus',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('taxNoticeFilename', FileType::class, [
                'label' => 'Avis d’imposition (original recto-verso)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('quittanceLoyer', FileType::class, [
                'label' => 'Dernière quittance de loyer',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('avisCharge', FileType::class, [
                'label' => 'Avis de charges',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('taxeFonciere', FileType::class, [
                'label' => 'Taxe foncière',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('fraisScolarite', FileType::class, [
                'label' => 'Frais de scolarité',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('attestationCaf', FileType::class, [
                'label' => 'Attestation CAF',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('otherDocumentFilename', FileType::class, [
                'label' => 'Autre document',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            ->add('privacyConsent', CheckboxType::class, [
                'label' => 'J’accepte le traitement de mes données personnelles',
                'required' => true,
                'constraints' => [
                    new Assert\IsTrue(['message' => 'Vous devez accepter la politique de confidentialité.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AidRequest::class,
            'is_family' => false,
        ]);
    }
}
