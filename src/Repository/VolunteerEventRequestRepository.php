<?php

namespace App\Repository;

use App\Entity\VolunteerEventRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VolunteerEventRequest>
 *
 * @method VolunteerEventRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method VolunteerEventRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method VolunteerEventRequest[]    findAll()
 * @method VolunteerEventRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VolunteerEventRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VolunteerEventRequest::class);
    }
}
