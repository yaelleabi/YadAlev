<?php

namespace App\Repository;

use App\Entity\FamilyEventRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FamilyEventRequest>
 */
class FamilyEventRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FamilyEventRequest::class);
    }
}
