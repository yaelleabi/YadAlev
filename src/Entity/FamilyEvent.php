<?php

namespace App\Entity;

use App\Repository\FamilyEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FamilyEventRepository::class)]
class FamilyEvent extends Event
{
    /**
     * @var Collection<int, Family>
     */
    #[ORM\ManyToMany(targetEntity: Family::class, inversedBy: 'familyEvents')]
    private Collection $assignedFamilies;

    #[ORM\Column]
    private ?int $Quantity = null;

    public function __construct()
    {
        $this->assignedFamilies = new ArrayCollection();
        $this->familyEventRequests = new ArrayCollection();
    }

    /**
     * @return Collection<int, Family>
     */
    public function getAssignedFamilies(): Collection
    {
        return $this->assignedFamilies;
    }

    public function addAssignedFamily(Family $assignedFamily): static
    {
        if (!$this->assignedFamilies->contains($assignedFamily)) {
            $this->assignedFamilies->add($assignedFamily);
        }

        return $this;
    }

    public function removeAssignedFamily(Family $assignedFamily): static
    {
        $this->assignedFamilies->removeElement($assignedFamily);

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->Quantity;
    }

    public function setQuantity(int $Quantity): static
    {
        $this->Quantity = $Quantity;

        return $this;
    }
    #[ORM\OneToMany(targetEntity: FamilyEventRequest::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $familyEventRequests;

    public function getFamilyEventRequests(): Collection
    {
        return $this->familyEventRequests;
    }

    public function addFamilyEventRequest(FamilyEventRequest $familyEventRequest): static
    {
        if (!$this->familyEventRequests->contains($familyEventRequest)) {
            $this->familyEventRequests->add($familyEventRequest);
            $familyEventRequest->setEvent($this);
        }

        return $this;
    }

    public function removeFamilyEventRequest(FamilyEventRequest $familyEventRequest): static
    {
        if ($this->familyEventRequests->removeElement($familyEventRequest)) {
            // set the owning side to null (unless already changed)
            if ($familyEventRequest->getEvent() === $this) {
                $familyEventRequest->setEvent(null);
            }
        }

        return $this;
    }
}
