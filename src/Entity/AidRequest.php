<?php

namespace App\Entity;

use App\Repository\FoodAssistanceRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\AidRequestStatus;

#[ORM\Entity(repositoryClass: FoodAssistanceRequestRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AidRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

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

    #[ORM\Embedded(class: Adress::class, columnPrefix: 'adress_')]
    #[Assert\Valid]
    private Adress $adress;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $housingStatus = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $maritalStatus = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\PositiveOrZero]
    private ?int $dependantsCount = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $employmentStatus = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $monthlyIncome = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $spouseEmploymentStatus = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $spouseMonthlyIncome = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $familyAllowanceAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $alimonyAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?string $rentAmountNetAide = null;

    #[ORM\Column(enumType: AidRequestStatus::class)]
    private AidRequestStatus $status;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $requestType = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $requestDuration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $otherRequestDuration = null;

   #[ORM\Column(type: Types::TEXT)]
   #[Assert\NotBlank]
    private ?string $requestReason = null;

    
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $urgencyExplanation = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 1, max: 10)]
    private ?int $urgencyLevel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $otherNeed = null;

  

    

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $otherComment = null;

    #[ORM\Column]
    #[Assert\IsTrue(message: "Vous devez consentir à la politique de confidentialité.")]
    private ?bool $privacyConsent = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $identityProofFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $incomeProofFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taxNoticeFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $otherDocumentFilename = null;

    #[ORM\ManyToOne(inversedBy: 'aidRequests')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Une famille doit être associée à cette demande.")]
    private ?Family $family = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $quittanceLoyer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avisCharge = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taxeFonciere = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fraisScolarite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $attestationCaf = null;

    public function __construct()
    {
        $this->adress = new Adress();
        $this->status = AidRequestStatus::PENDING;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // ---------------- Getters & Setters ---------------- //

    public function getId(): ?int { return $this->id; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(?string $lastName): self { $this->lastName = $lastName; return $this; }

    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(?string $firstName): self { $this->firstName = $firstName; return $this; }

    public function getDateOfBirth(): ?\DateTimeInterface { return $this->dateOfBirth; }
    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self { $this->dateOfBirth = $dateOfBirth; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }

    public function getPhoneNumber(): ?string { return $this->phoneNumber; }
    public function setPhoneNumber(?string $phoneNumber): self { $this->phoneNumber = $phoneNumber; return $this; }

    public function getAdress(): Adress { return $this->adress; }
    public function setAdress(Adress $adress): self { $this->adress = $adress; return $this; }

    public function getHousingStatus(): ?string { return $this->housingStatus; }
    public function setHousingStatus(?string $housingStatus): self { $this->housingStatus = $housingStatus; return $this; }

    public function getMaritalStatus(): ?string { return $this->maritalStatus; }
    public function setMaritalStatus(?string $maritalStatus): self { $this->maritalStatus = $maritalStatus; return $this; }

    public function getDependantsCount(): ?int { return $this->dependantsCount; }
    public function setDependantsCount(?int $dependantsCount): self { $this->dependantsCount = $dependantsCount; return $this; }

    public function getEmploymentStatus(): ?string { return $this->employmentStatus; }
    public function setEmploymentStatus(?string $employmentStatus): self { $this->employmentStatus = $employmentStatus; return $this; }

    public function getMonthlyIncome(): ?string { return $this->monthlyIncome; }
    public function setMonthlyIncome(?string $monthlyIncome): self { $this->monthlyIncome = $monthlyIncome; return $this; }

    public function getSpouseEmploymentStatus(): ?string { return $this->spouseEmploymentStatus; }
    public function setSpouseEmploymentStatus(?string $spouseEmploymentStatus): self { $this->spouseEmploymentStatus = $spouseEmploymentStatus; return $this; }

    public function getSpouseMonthlyIncome(): ?string { return $this->spouseMonthlyIncome; }
    public function setSpouseMonthlyIncome(?string $spouseMonthlyIncome): self { $this->spouseMonthlyIncome = $spouseMonthlyIncome; return $this; }

    public function getFamilyAllowanceAmount(): ?string { return $this->familyAllowanceAmount; }
    public function setFamilyAllowanceAmount(?string $familyAllowanceAmount): self { $this->familyAllowanceAmount = $familyAllowanceAmount; return $this; }

    public function getAlimonyAmount(): ?string { return $this->alimonyAmount; }
    public function setAlimonyAmount(?string $alimonyAmount): self { $this->alimonyAmount = $alimonyAmount; return $this; }

    public function getRentAmountNetAide(): ?string { return $this->rentAmountNetAide; }
    public function setRentAmountNetAide(?string $rentAmountNetAide): self { $this->rentAmountNetAide = $rentAmountNetAide; return $this; }

    public function getStatus(): AidRequestStatus { return $this->status; }
    public function setStatus(AidRequestStatus $status): self { $this->status = $status; return $this; }

    public function getRequestType(): ?string { return $this->requestType; }
    public function setRequestType(?string $requestType): self { $this->requestType = $requestType; return $this; }

    public function getRequestDuration(): ?string { return $this->requestDuration; }
    public function setRequestDuration(?string $requestDuration): self { $this->requestDuration = $requestDuration; return $this; }

    public function getOtherRequestDuration(): ?string { return $this->otherRequestDuration; }
    public function setOtherRequestDuration(?string $otherRequestDuration): self { $this->otherRequestDuration = $otherRequestDuration; return $this; }

    public function getRequestReason(): ?string { return $this->requestReason; }
    public function setRequestReason(?string $requestReason): self { $this->requestReason = $requestReason; return $this; }

   

    public function getUrgencyExplanation(): ?string { return $this->urgencyExplanation; }
    public function setUrgencyExplanation(?string $urgencyExplanation): self { $this->urgencyExplanation = $urgencyExplanation; return $this; }

    public function getUrgencyLevel(): ?int { return $this->urgencyLevel; }
    public function setUrgencyLevel(?int $urgencyLevel): self { $this->urgencyLevel = $urgencyLevel; return $this; }

    public function getOtherNeed(): ?string { return $this->otherNeed; }
    public function setOtherNeed(?string $otherNeed): self { $this->otherNeed = $otherNeed; return $this; }

    

    
    public function getOtherComment(): ?string { return $this->otherComment; }
    public function setOtherComment(?string $otherComment): self { $this->otherComment = $otherComment; return $this; }

    public function getPrivacyConsent(): ?bool { return $this->privacyConsent; }
    public function setPrivacyConsent(?bool $privacyConsent): self { $this->privacyConsent = $privacyConsent; return $this; }

    public function getIdentityProofFilename(): ?string { return $this->identityProofFilename; }
    public function setIdentityProofFilename(?string $identityProofFilename): self { $this->identityProofFilename = $identityProofFilename; return $this; }

    public function getIncomeProofFilename(): ?string { return $this->incomeProofFilename; }
    public function setIncomeProofFilename(?string $incomeProofFilename): self { $this->incomeProofFilename = $incomeProofFilename; return $this; }

    public function getTaxNoticeFilename(): ?string { return $this->taxNoticeFilename; }
    public function setTaxNoticeFilename(?string $taxNoticeFilename): self { $this->taxNoticeFilename = $taxNoticeFilename; return $this; }

    public function getOtherDocumentFilename(): ?string { return $this->otherDocumentFilename; }
    public function setOtherDocumentFilename(?string $otherDocumentFilename): self { $this->otherDocumentFilename = $otherDocumentFilename; return $this; }

    public function getFamily(): ?family { return $this->family; }
    public function setFamily(?family $family): self { $this->family = $family; return $this; }

    public function getQuittanceLoyer(): ?string
    {
        return $this->quittanceLoyer;
    }

    public function setQuittanceLoyer(string $quittanceLoyer): static
    {
        $this->quittanceLoyer = $quittanceLoyer;

        return $this;
    }

    public function getAvisCharge(): ?string
    {
        return $this->avisCharge;
    }

    public function setAvisCharge(string $avisCharge): static
    {
        $this->avisCharge = $avisCharge;

        return $this;
    }

    public function getTaxeFonciere(): ?string
    {
        return $this->taxeFonciere;
    }

    public function setTaxeFonciere(string $taxeFonciere): static
    {
        $this->taxeFonciere = $taxeFonciere;

        return $this;
    }

    public function getFraisScolarite(): ?string
    {
        return $this->fraisScolarite;
    }

    public function setFraisScolarite(string $fraisScolarite): static
    {
        $this->fraisScolarite = $fraisScolarite;

        return $this;
    }

    public function getAttestationCaf(): ?string
    {
        return $this->attestationCaf;
    }

    public function setAttestationCaf(string $attestationCaf): static
    {
        $this->attestationCaf = $attestationCaf;

        return $this;
    }
}
