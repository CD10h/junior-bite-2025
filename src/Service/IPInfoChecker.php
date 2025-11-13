<?php

namespace App\Service;

use App\Entity\SavedIPInfo;
use App\Repository\SavedIPInfoRepository;
use Doctrine\ORM\EntityManagerInterface;

class IPInfoChecker
{

    public function __construct(
        private IPStackService $ipStack,
        private SavedIPInfoRepository $ipRepository,
        private EntityManagerInterface $entityManager
    ) {}


    /**
     * Check information on IP address, calling an external API if needed
     * 
     * @param string $ip
     * @return SavedIPInfo
     */
    public function checkIpInfo(string $ip): SavedIPInfo

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
            $savedIP = new SavedIPInfo();
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


    /**
     * Delete Saved IP data, returns false if not found
     * 
     * @param string $ip
     * @return bool
     */
    public function deleteSavedIPData(string $ip): bool
    {
        $ipInfo = $this->ipRepository->findOneByIp($ip);

        if (!$ipInfo) {
            return false;
        }

        $this->entityManager->remove($ipInfo);
        $this->entityManager->flush();

        return true;
    }
}
