<?php

namespace App\Entity;

use App\Repository\FamilyEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FamilyEventRepository::class)]
class FamilyEvent extends Event
{
    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $assignedFamilies = null;

    public function getAssignedFamilies(): ?array
    {
        return $this->assignedFamilies;
    }

    public function setAssignedFamilies(?array $assignedFamilies): static
    {
        $this->assignedFamilies = $assignedFamilies;

        return $this;
    }
}
