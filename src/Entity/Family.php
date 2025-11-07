<?php

namespace App\Entity;

use App\Repository\FamilyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\AidList;
use App\Entity\AidRequest;

#[ORM\Entity(repositoryClass: FamilyRepository::class)]
class Family extends User
{
    /**
     * @var Collection<int, AidList>
     */
    #[ORM\ManyToMany(targetEntity: AidList::class, inversedBy: 'families')]
    private Collection $aidList;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?AidRequest $aidRequest = null;

    public function __construct()
    {
        $this->aidList = new ArrayCollection();
    }

    /**
     * @return Collection<int, AidList>
     */
    public function getAidList(): Collection
    {
        return $this->aidList;
    }

    public function addAidList(AidList $aidList): static
    {
        if (!$this->aidList->contains($aidList)) {
            $this->aidList->add($aidList);
        }

        return $this;
    }

    public function removeAidList(AidList $aidList): static
    {
        $this->aidList->removeElement($aidList);

        return $this;
    }

    public function getAidRequest(): ?AidRequest
    {
        return $this->aidRequest;
    }

    public function setAidRequest(?AidRequest $aidRequest): static
    {
        $this->aidRequest = $aidRequest;

        return $this;
    }
}
