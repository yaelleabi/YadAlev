<?php

namespace App\Entity;

use App\Repository\FamilyEventRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FamilyEventRequestRepository::class)]
class FamilyEventRequest
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_ACCEPTED = 'ACCEPTED';
    public const STATUS_REFUSED = 'REFUSED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'familyEventRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Family $family = null;

    #[ORM\ManyToOne(inversedBy: 'familyEventRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FamilyEvent $event = null;

    #[ORM\Column(length: 20)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFamily(): ?Family
    {
        return $this->family;
    }

    public function setFamily(?Family $family): static
    {
        $this->family = $family;

        return $this;
    }

    public function getEvent(): ?FamilyEvent
    {
        return $this->event;
    }

    public function setEvent(?FamilyEvent $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
