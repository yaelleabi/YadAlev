<?php

namespace App\Entity;

use App\Repository\VolunteerEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VolunteerEventRepository::class)]
class VolunteerEvent extends Event
{
    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $assignedVolunteers = null;

    public function getAssignedVolunteers(): ?array
    {
        return $this->assignedVolunteers;
    }

    public function setAssignedVolunteers(?array $assignedVolunteers): static
    {
        $this->assignedVolunteers = $assignedVolunteers;

        return $this;
    }
}
