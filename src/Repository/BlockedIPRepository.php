<?php

namespace App\Repository;

use App\Entity\BlockedIP;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlockedIP>
 */
class BlockedIPRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlockedIP::class);
    }

    public function findOneByIp(string $ip): ?BlockedIP
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.ip = :val')
            ->setParameter('val', $ip)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
