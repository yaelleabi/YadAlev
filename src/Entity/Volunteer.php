<?php

namespace App\Entity;

use App\Repository\VolunteerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VolunteerRepository::class)]
class Volunteer extends User
{
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
