<?php

namespace App\Entity;

use App\Repository\VolunteerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VolunteerRepository::class)]
class Volunteer Extends User
{
    #[ORM\Column(length: 255)]
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
