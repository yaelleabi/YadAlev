<?php

namespace App\Repository;

use App\Entity\AidRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Entity\Family;
use App\Enum\AidRequestStatus;
/**
 * @extends ServiceEntityRepository<AidRequest>
 */
class AidRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AidRequest::class);
    }

    public function findLatestByFamily(User $family)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.family = :family')
            ->setParameter('family', $family)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findValidatedByFamily(Family $family): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.family = :family')
            ->andWhere('a.status = :status')
            ->setParameter('family', $family)
            ->setParameter('status', AidRequestStatus::VALIDATED)
            ->orderBy('a.createdAt', 'DESC')   // si tu as un updatedAt
            ->getQuery()
            ->getResult();
    }
    public function findFiltered(?string $year, ?string $status)
    {
        $qb = $this->createQueryBuilder('a');

        // Filtre année
        if ($year) {
            $start = new \DateTime("$year-01-01 00:00:00");
            $end = new \DateTime("$year-12-31 23:59:59");

            $qb->andWhere('a.createdAt BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);
        }

        // Filtre statut (CORRIGÉ)
        if ($status) {
            $qb->andWhere('a.status = :status')
            ->setParameter('status', $status);
        }

        return $qb->orderBy('a.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
    }

    public function findAvailableYears(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT DISTINCT SUBSTRING(created_at, 1, 4) AS year 
                FROM aid_request
                ORDER BY year DESC";

        $result = $conn->executeQuery($sql)->fetchAllAssociative();

        return array_column($result, 'year');
    }



    //    /**
    //     * @return AidRequest[] Returns an array of AidRequest objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?AidRequest
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
