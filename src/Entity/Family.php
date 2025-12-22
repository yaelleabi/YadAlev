<?php

namespace App\Entity;

use App\Repository\FamilyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\AidRequest;
use App\Entity\User;
use App\Entity\Adress;

#[ORM\Entity(repositoryClass: FamilyRepository::class)]
class Family extends User
{
    /* ======================= CHAMPS SOCIAUX (venant de AidRequest) ======================= */

    #[ORM\Embedded(class: Adress::class, columnPrefix: 'adress_')]
    protected Adress $adress;
        #[ORM\Column(length: 100, nullable: true)]
    protected ?string $firstName = null;

    
    #[ORM\Column(type: "date", nullable: true)]
    protected ?\DateTimeInterface $dateOfBirth = null;
    


    #[ORM\Column(length: 50, nullable: true)]
    protected ?string $housingStatus = null;

    #[ORM\Column(length: 50, nullable: true)]
    protected ?string $maritalStatus = null;

    #[ORM\Column(type: "integer", nullable: true)]
    protected ?int $dependantsCount = null;

    #[ORM\Column(length: 50, nullable: true)]
    protected ?string $employmentStatus = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    protected ?string $monthlyIncome = null;

    #[ORM\Column(length: 50, nullable: true)]
    protected ?string $spouseEmploymentStatus = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    protected ?string $spouseMonthlyIncome = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    protected ?string $familyAllowanceAmount = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    protected ?string $alimonyAmount = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    protected ?string $rentAmountNetAide = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $otherNeed = null;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $otherComment = null;

    /* ======================= DOCUMENTS ======================= */

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $identityProofFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $incomeProofFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $taxNoticeFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $otherDocumentFilename = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $quittanceLoyer = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $avisCharge = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $taxeFonciere = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $fraisScolarite = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $attestationCaf = null;

    /* ======================= RELATIONS ======================= */

    /**
     * @var Collection<int, AidRequest>
     */

    #[ORM\OneToMany(targetEntity: AidRequest::class, mappedBy: 'family')]
    private Collection $aidRequests;

    /**
     * @var Collection<int, FamilyEvent>
     */
    #[ORM\ManyToMany(targetEntity: FamilyEvent::class, mappedBy: 'assignedFamilies')]
    private Collection $familyEvents;

    public function __construct()
    {
        parent::__construct();
       
        $this->aidRequests = new ArrayCollection();
        $this->adress = new Adress();
        $this->familyEvents = new ArrayCollection();
    }

    /* ======================= GETTERS / SETTERS ======================= */

    public function getAdress(): Adress { return $this->adress; }
    public function setAdress(Adress $adress): self { $this->adress = $adress; return $this; }

    public function getHousingStatus(): ?string { return $this->housingStatus; }
    public function setHousingStatus(?string $s): self { $this->housingStatus = $s; return $this; }

    public function getMaritalStatus(): ?string { return $this->maritalStatus; }
    public function setMaritalStatus(?string $s): self { $this->maritalStatus = $s; return $this; }

    public function getDependantsCount(): ?int { return $this->dependantsCount; }
    public function setDependantsCount(?int $i): self { $this->dependantsCount = $i; return $this; }

    public function getEmploymentStatus(): ?string { return $this->employmentStatus; }
    public function setEmploymentStatus(?string $e): self { $this->employmentStatus = $e; return $this; }

    public function getMonthlyIncome(): ?string { return $this->monthlyIncome; }
    public function setMonthlyIncome(?string $v): self { $this->monthlyIncome = $v; return $this; }

    public function getSpouseEmploymentStatus(): ?string { return $this->spouseEmploymentStatus; }
    public function setSpouseEmploymentStatus(?string $s): self { $this->spouseEmploymentStatus = $s; return $this; }

    public function getSpouseMonthlyIncome(): ?string { return $this->spouseMonthlyIncome; }
    public function setSpouseMonthlyIncome(?string $v): self { $this->spouseMonthlyIncome = $v; return $this; }

    public function getFamilyAllowanceAmount(): ?string { return $this->familyAllowanceAmount; }
    public function setFamilyAllowanceAmount(?string $v): self { $this->familyAllowanceAmount = $v; return $this; }

    public function getAlimonyAmount(): ?string { return $this->alimonyAmount; }
    public function setAlimonyAmount(?string $v): self { $this->alimonyAmount = $v; return $this; }

    public function getRentAmountNetAide(): ?string { return $this->rentAmountNetAide; }
    public function setRentAmountNetAide(?string $v): self { $this->rentAmountNetAide = $v; return $this; }

    public function getOtherNeed(): ?string { return $this->otherNeed; }
    public function setOtherNeed(?string $v): self { $this->otherNeed = $v; return $this; }

    public function getOtherComment(): ?string { return $this->otherComment; }
    public function setOtherComment(?string $v): self { $this->otherComment = $v; return $this; }

    /* ===== DOCUMENTS ===== */

    public function getIdentityProofFilename(): ?string { return $this->identityProofFilename; }
    public function setIdentityProofFilename(?string $v): self { $this->identityProofFilename = $v; return $this; }

    public function getIncomeProofFilename(): ?string { return $this->incomeProofFilename; }
    public function setIncomeProofFilename(?string $v): self { $this->incomeProofFilename = $v; return $this; }

    public function getTaxNoticeFilename(): ?string { return $this->taxNoticeFilename; }
    public function setTaxNoticeFilename(?string $v): self { $this->taxNoticeFilename = $v; return $this; }

    public function getOtherDocumentFilename(): ?string { return $this->otherDocumentFilename; }
    public function setOtherDocumentFilename(?string $v): self { $this->otherDocumentFilename = $v; return $this; }

    public function getQuittanceLoyer(): ?string { return $this->quittanceLoyer; }
    public function setQuittanceLoyer(?string $v): self { $this->quittanceLoyer = $v; return $this; }

    public function getAvisCharge(): ?string { return $this->avisCharge; }
    public function setAvisCharge(?string $v): self { $this->avisCharge = $v; return $this; }

    public function getTaxeFonciere(): ?string { return $this->taxeFonciere; }
    public function setTaxeFonciere(?string $v): self { $this->taxeFonciere = $v; return $this; }

    public function getFraisScolarite(): ?string { return $this->fraisScolarite; }
    public function setFraisScolarite(?string $v): self { $this->fraisScolarite = $v; return $this; }

    public function getAttestationCaf(): ?string { return $this->attestationCaf; }
    public function setAttestationCaf(?string $v): self { $this->attestationCaf = $v; return $this; }
    /* ===== NOM & PRÉNOM ===== */
    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(?string $fn): self { $this->firstName = $fn; return $this; }


    /* ===== DATE NAISSANCE ===== */
    public function getDateOfBirth(): ?\DateTimeInterface { return $this->dateOfBirth; }
    public function setDateOfBirth(?\DateTimeInterface $d): self { $this->dateOfBirth = $d; return $this; }
    /** ======================= RELATION AIDREQUEST ======================= */

    public function getAidRequests(): Collection
    {
        return $this->aidRequests;
    }

    public function addAidRequest(AidRequest $aidRequest): self
    {
        if (!$this->aidRequests->contains($aidRequest)) {
            $this->aidRequests->add($aidRequest);
            $aidRequest->setFamily($this);
        }
        return $this;
    }

    public function removeAidRequest(AidRequest $aidRequest): self
    {
        if ($this->aidRequests->removeElement($aidRequest)) {
            if ($aidRequest->getFamily() === $this) {
                $aidRequest->setFamily(null);
            }
        }
        return $this;
    }
    

    /**
     * @return Collection<int, FamilyEvent>
     */
    public function getFamilyEvents(): Collection
    {
        return $this->familyEvents;
    }

    public function addFamilyEvent(FamilyEvent $familyEvent): static
    {
        if (!$this->familyEvents->contains($familyEvent)) {
            $this->familyEvents->add($familyEvent);
            $familyEvent->addAssignedFamily($this);
        }

        return $this;
    }

    public function removeFamilyEvent(FamilyEvent $familyEvent): static
    {
        if ($this->familyEvents->removeElement($familyEvent)) {
            $familyEvent->removeAssignedFamily($this);
        }

        return $this;
    }



}
