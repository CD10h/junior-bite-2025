<?php

namespace App\Repository;

use App\Entity\SavedIPInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavedIPInfo>
 */
class SavedIPInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedIPInfo::class);
    }


    /**
     * Find single blocked IP record
     * 
     * @param string $ip
     * @return ?SavedIPInfo
     */
    public function findOneByIp(string $ip): ?SavedIPInfo
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.ip = :val')
            ->setParameter('val', $ip)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
