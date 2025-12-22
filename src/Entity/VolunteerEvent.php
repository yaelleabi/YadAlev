<?php

namespace App\Entity;

use App\Repository\VolunteerEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VolunteerEventRepository::class)]
class VolunteerEvent extends Event
{
    /**
     * @var Collection<int, Volunteer>
     */
    #[ORM\ManyToMany(targetEntity: Volunteer::class, inversedBy: 'volunteerEvents')]
    private Collection $assignedVolunteers;

    public function __construct()
    {
        $this->assignedVolunteers = new ArrayCollection();
    }

    /**
     * @return Collection<int, Volunteer>
     */
    public function getAssignedVolunteers(): Collection
    {
        return $this->assignedVolunteers;
    }

    public function addAssignedVolunteer(Volunteer $assignedVolunteer): static
    {
        if (!$this->assignedVolunteers->contains($assignedVolunteer)) {
            $this->assignedVolunteers->add($assignedVolunteer);
        }

        return $this;
    }

    public function removeAssignedVolunteer(Volunteer $assignedVolunteer): static
    {
        $this->assignedVolunteers->removeElement($assignedVolunteer);

        return $this;
    }
}
