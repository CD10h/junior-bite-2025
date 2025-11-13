<?php

namespace App\Repository;

use App\Entity\SavedIP;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavedIP>
 */
class SavedIPRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedIP::class);
    }

    //    /**
    //     * @return SavedIP[] Returns an array of SavedIP objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findOneByIp(string $ip): ?SavedIP
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.ip = :val')
            ->setParameter('val', $ip)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
