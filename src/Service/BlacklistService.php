<?php

namespace App\Service;

use App\Entity\BlockedIP;
use App\Repository\BlockedIPRepository;
use Doctrine\ORM\EntityManagerInterface;

class BlacklistService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private BlockedIPRepository $blockedIPRepository,

    ) {}


    /**
     * Add IP to blacklist
     * 
     * @param string $ip
     * @return void
     */
    public function blacklistIP(string $ip): void
    {

        if ($this->blockedIPRepository->findOneByIp($ip)) {
            return;
        }

        $blockedIP = new BlockedIP();
        $blockedIP->setIp($ip);

        $this->entityManager->persist($blockedIP);
        $this->entityManager->flush();
    }


    /**
     * Returns true if IP was found and removed
     * @param string $ip
     * @return bool
     */
    public function unblockIP(string $ip): bool
    {
        $foundIP = $this->blockedIPRepository->findOneByIp($ip);
        if ($foundIP) {
            $this->entityManager->remove($foundIP);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }
}
