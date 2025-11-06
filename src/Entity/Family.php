<?php

namespace App\Entity;

use App\Repository\FamilyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FamilyRepository::class)]
class Family extends User
{
   /* #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;*/

    /**
     * @var Collection<int, aidList>
     */
    #[ORM\ManyToMany(targetEntity: aidList::class, inversedBy: 'families')]
    private Collection $aidList;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?aidRequest $aidRequest = null;

    public function __construct()
    {
        $this->aidList = new ArrayCollection();
    }

    /*public function getId(): ?int
    {
        return $this->id;
    }*/
    /**
     * @return Collection<int, aidList>
     */
    public function getAidList(): Collection
    {
        return $this->aidList;
    }

    public function addAidList(aidList $aidList): static
    {
        if (!$this->aidList->contains($aidList)) {
            $this->aidList->add($aidList);
        }

        return $this;
    }

    public function removeAidList(aidList $aidList): static
    {
        $this->aidList->removeElement($aidList);

        return $this;
    }

    public function getAidRequest(): ?aidRequest
    {
        return $this->aidRequest;
    }

    public function setAidRequest(?aidRequest $aidRequest): static
    {
        $this->aidRequest = $aidRequest;

        return $this;
    }
}
