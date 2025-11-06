<?php

// src/Entity/FoodAssistanceRequest.php
namespace App\Entity;

use App\Repository\FoodAssistanceRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\AidRequestStatus;


#[ORM\Entity(repositoryClass: FoodAssistanceRequestRepository::class)]
#[ORM\HasLifecycleCallbacks] // Pour gérer la date de création
class AidRequest
{
    // --- PARTIE 1 : PROPRIÉTÉS TECHNIQUES (ID, DATES) ---
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    // --- PARTIE 2 : INFORMATIONS PERSONNELLES ---

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $phoneNumber = null;

    // Utilisation de l'Embeddable Address pour l'adresse postale complète
    #[ORM\Embedded(class: Adress::class, columnPrefix: 'adress_')]
    #[Assert\Valid] // Assure que les contraintes de l'Address sont vérifiées
    private Adress $adress;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $housingStatus = null; // Ex: Propriétaire, Locataire, Hébergé

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $maritalStatus = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?int $dependantsCount = null; // Nombre de personnes à charge

    // --- PARTIE 3 : INFORMATIONS FINANCIÈRES ---

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $employmentStatus = null; // Situation professionnelle

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $monthlyIncome = null; // Revenus mensuel (salaire)

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $spouseEmploymentStatus = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $spouseMonthlyIncome = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $familyAllowanceAmount = null; // Montant des allocations familiales

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $alimonyAmount = null; // Montant de la pension alimentaire

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $rentAmountNetAide = null; // Montant du loyer (sous déduction des APL)
    //statut de la demande
   
    #[ORM\Column(enumType: AidRequestStatus::class)]
     private AidRequestStatus $status;

    // --- PARTIE 4 : NATURE DE LA DEMANDE ---

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $requestType = null; // Type de demande (Colis, Bon alimentaire, etc.)

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $requestDuration = null; // Durée de la demande (ex: 1 mois, 3 mois, Autre)

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $otherRequestDuration = null; // Si "Autre" préciser

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $requestReason = null; // Raison de la demande

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $otherRequestReason = null; // Si "Autre" indiquer

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $urgencyExplanation = null; // En quoi la demande est urgente

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 1, max: 10)]
    private ?int $urgencyLevel = null; // Urgence de 1 à 10

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $otherNeed = null; // Autre besoin à soumettre

    // --- PARTIE 5 : SITUATION ACTUELLE ---

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $currentSituation = null; // Explication de la situation actuelle

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $financialDifficulties = null; // Précisez les difficultés financières

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $otherComment = null;

    // --- PARTIE 6 : CONSENTEMENT ET FICHIERS (Métadonnées de fichier) ---

    #[ORM\Column]
    #[Assert\IsTrue(message: "Vous devez consentir à la politique de confidentialité.")]
    private ?bool $privacyConsent = false;

    // Les chemins ou noms des fichiers (les fichiers eux-mêmes seront stockés sur le disque/S3)
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $identityProofFilename = null; 

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $incomeProofFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taxNoticeFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $otherDocumentFilename = null;


    // -----------------------------------------------------------------

    public function __construct()
    {
        // Initialiser les objets non-nullables
        $this->address = new Adress();
        $this->status = AidRequestStatus::PENDING;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // L'adresse est gérée par son getter/setter unique
    public function getAdress(): Adress
    {
        return $this->adress;
    }

    public function setAdress(Adress $adress): self
    {
        $this->adress = $adress;
        return $this;
    }
    
    // ... Générer tous les autres getters et setters ...
}