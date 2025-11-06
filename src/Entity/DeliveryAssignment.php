<?php

namespace App\Entity;

use App\Enum\DeliveryAssignmentStatus;
use App\Repository\DeliveryAssignmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryAssignmentRepository::class)]
class DeliveryAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'deliveryAssignments')]
    private ?Delivery $delivery = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column]
    private ?int $totalPackages = null;

    #[ORM\Column]
    private ?int $reservedPackages = null;

    #[ORM\Column(enumType: DeliveryAssignmentStatus::class)]
    private ?DeliveryAssignmentStatus $deliveryStatus = null;

    /**
     * @var Collection<int, VolunteerRequest>
     */
    #[ORM\OneToMany(targetEntity: VolunteerRequest::class, mappedBy: 'deliveryAssignment')]
    private Collection $volunteerRequests;

    public function __construct()
    {
        $this->volunteerRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): static
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getTotalPackages(): ?int
    {
        return $this->totalPackages;
    }

    public function setTotalPackages(int $totalPackages): static
    {
        $this->totalPackages = $totalPackages;

        return $this;
    }

    public function getReservedPackages(): ?int
    {
        return $this->reservedPackages;
    }

    public function setReservedPackages(int $reservedPackages): static
    {
        $this->reservedPackages = $reservedPackages;

        return $this;
    }

    public function getDeliveryStatus(): ?DeliveryAssignmentStatus
    {
        return $this->deliveryStatus;
    }

    public function setDeliveryStatus(DeliveryAssignmentStatus $deliveryStatus): static
    {
        $this->deliveryStatus = $deliveryStatus;

        return $this;
    }

    /**
     * @return Collection<int, VolunteerRequest>
     */
    public function getVolunteerRequests(): Collection
    {
        return $this->volunteerRequests;
    }

    public function addVolunteerRequest(VolunteerRequest $volunteerRequest): static
    {
        if (!$this->volunteerRequests->contains($volunteerRequest)) {
            $this->volunteerRequests->add($volunteerRequest);
            $volunteerRequest->setDeliveryAssignment($this);
        }

        return $this;
    }

    public function removeVolunteerRequest(VolunteerRequest $volunteerRequest): static
    {
        if ($this->volunteerRequests->removeElement($volunteerRequest)) {
            // set the owning side to null (unless already changed)
            if ($volunteerRequest->getDeliveryAssignment() === $this) {
                $volunteerRequest->setDeliveryAssignment(null);
            }
        }

        return $this;
    }
}
