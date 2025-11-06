<?php

namespace App\Entity;

use App\Enum\VolunteerRequestStatus;
use App\Repository\VolunteerRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VolunteerRequestRepository::class)]
class VolunteerRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'volunteerRequests')]
    private ?DeliveryAssignment $deliveryAssignment = null;

    #[ORM\Column(enumType: VolunteerRequestStatus::class)]
    private ?VolunteerRequestStatus $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeliveryAssignment(): ?DeliveryAssignment
    {
        return $this->deliveryAssignment;
    }

    public function setDeliveryAssignment(?DeliveryAssignment $deliveryAssignment): static
    {
        $this->deliveryAssignment = $deliveryAssignment;

        return $this;
    }

    public function getStatus(): ?VolunteerRequestStatus
    {
        return $this->status;
    }

    public function setStatus(VolunteerRequestStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}
