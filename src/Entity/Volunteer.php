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

    #[ORM\OneToMany(mappedBy: 'volunteer', targetEntity: VolunteerEventRequest::class, orphanRemoval: true)]
    private Collection $volunteerEventRequests;

    public function __construct()
    {
        parent::__construct();
        $this->volunteerEvents = new ArrayCollection();
        $this->volunteerEventRequests = new ArrayCollection();
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

    /**
     * @return Collection<int, VolunteerEventRequest>
     */
    public function getVolunteerEventRequests(): Collection
    {
        return $this->volunteerEventRequests;
    }

    public function addVolunteerEventRequest(VolunteerEventRequest $volunteerEventRequest): static
    {
        if (!$this->volunteerEventRequests->contains($volunteerEventRequest)) {
            $this->volunteerEventRequests->add($volunteerEventRequest);
            $volunteerEventRequest->setVolunteer($this);
        }

        return $this;
    }

    public function removeVolunteerEventRequest(VolunteerEventRequest $volunteerEventRequest): static
    {
        if ($this->volunteerEventRequests->removeElement($volunteerEventRequest)) {
            // set the owning side to null (unless already changed)
            if ($volunteerEventRequest->getVolunteer() === $this) {
                $volunteerEventRequest->setVolunteer(null);
            }
        }

        return $this;
    }
}
