<?php

namespace App\Entity;

use App\Repository\VolunteerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: VolunteerRepository::class)]
class Volunteer Extends User
{
    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?string $assignedProjects = null;

    /**
     * @var Collection<int, VolunteerEvent>
     */
    #[ORM\ManyToMany(targetEntity: VolunteerEvent::class, mappedBy: 'assignedVolunteers')]
    private Collection $volunteerEvents;

    public function __construct()
    {
        parent::__construct();
        $this->volunteerEvents = new ArrayCollection();
    }

    public function getAssignedProjects(): ?string
    {
        return $this->assignedProjects;
    }

    public function setAssignedProjects(string $assignedProjects): static
    {
        $this->assignedProjects = $assignedProjects;

        return $this;
    }

    /**
     * @return Collection<int, VolunteerEvent>
     */
    public function getVolunteerEvents(): Collection
    {
        return $this->volunteerEvents;
    }

    public function addVolunteerEvent(VolunteerEvent $volunteerEvent): static
    {
        if (!$this->volunteerEvents->contains($volunteerEvent)) {
            $this->volunteerEvents->add($volunteerEvent);
            $volunteerEvent->addAssignedVolunteer($this);
        }

        return $this;
    }

    public function removeVolunteerEvent(VolunteerEvent $volunteerEvent): static
    {
        if ($this->volunteerEvents->removeElement($volunteerEvent)) {
            $volunteerEvent->removeAssignedVolunteer($this);
        }

        return $this;
    }
}
