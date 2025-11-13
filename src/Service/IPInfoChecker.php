<?php

namespace App\Service;

use App\Entity\SavedIP;
use App\Repository\SavedIPRepository;
use Doctrine\ORM\EntityManagerInterface;

class IPInfoChecker
{

    public function __construct(
        private IPStackService $ipStack,
        private SavedIPRepository $ipRepository,
        private EntityManagerInterface $entityManager
    ) {}


    public function checkIpInfo(string $ip): SavedIP

    {
        // Check if IP info is cached
        $savedIP = $this->ipRepository->findOneByIp($ip);

        if ($savedIP) {
            $now = new \DateTimeImmutable();
            $interval = $now->diff($savedIP->getLastUpdated());
            if ($interval->days < 1) {
                return $savedIP;
            }
        }

        $ipData = $this->ipStack->fetchIpInfo($ip);


        if (!$savedIP) {
            $savedIP = new \App\Entity\SavedIP();
            $savedIP->setIp($ip);
        }

        $savedIP->setType($ipData->getType());
        $savedIP->setContinentCode($ipData->getContinentCode());
        $savedIP->setCountryCode($ipData->getCountryCode());
        $savedIP->setRegionCode($ipData->getRegionCode());
        $savedIP->setCity($ipData->getCity());
        $savedIP->setLatitude($ipData->getLatitude());
        $savedIP->setLongitude($ipData->getLongitude());

        // Persist changes
        $this->entityManager->persist($savedIP);
        $this->entityManager->flush();

        return $savedIP;
    }
}
