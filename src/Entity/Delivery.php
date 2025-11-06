<?php

namespace App\Entity;

use App\Enum\DeliveryStatus;
use App\Repository\DeliveryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\Column(enumType: DeliveryStatus::class)]
    private ?DeliveryStatus $status = null;

    /**
     * @var Collection<int, DeliveryAssignment>
     */
    #[ORM\OneToMany(targetEntity: DeliveryAssignment::class, mappedBy: 'delivery')]
    private Collection $deliveryAssignments;

    public function __construct()
    {
        $this->deliveryAssignments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?DeliveryStatus
    {
        return $this->status;
    }

    public function setStatus(DeliveryStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, DeliveryAssignment>
     */
    public function getDeliveryAssignments(): Collection
    {
        return $this->deliveryAssignments;
    }

    public function addDeliveryAssignment(DeliveryAssignment $deliveryAssignment): static
    {
        if (!$this->deliveryAssignments->contains($deliveryAssignment)) {
            $this->deliveryAssignments->add($deliveryAssignment);
            $deliveryAssignment->setDelivery($this);
        }

        return $this;
    }

    public function removeDeliveryAssignment(DeliveryAssignment $deliveryAssignment): static
    {
        if ($this->deliveryAssignments->removeElement($deliveryAssignment)) {
            // set the owning side to null (unless already changed)
            if ($deliveryAssignment->getDelivery() === $this) {
                $deliveryAssignment->setDelivery(null);
            }
        }

        return $this;
    }
}
