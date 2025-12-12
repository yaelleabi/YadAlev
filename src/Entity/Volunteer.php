<?php

namespace App\Entity;

use App\Repository\VolunteerRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: VolunteerRepository::class)]
class Volunteer Extends User
{
    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?string $assignedProjects = null;

    public function getAssignedProjects(): ?string
    {
        return $this->assignedProjects;
    }

    public function setAssignedProjects(string $assignedProjects): static
    {
        $this->assignedProjects = $assignedProjects;

        return $this;
    }
}
