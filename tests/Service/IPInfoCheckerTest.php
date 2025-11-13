<?php

namespace App\Tests\Service;

use App\Entity\SavedIPInfo;
use App\Service\IPInfoChecker;
use App\Service\IPStackService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class IPInfoCheckerTest extends TestCase
{

    public function testCheckIpInfoReturnsCachedData(): void
    {

        $mockIPRepository = $this->createMock(\App\Repository\SavedIPInfoRepository::class);
        $mockIPRepository->method('findOneByIp')
            ->willReturn(new SavedIPInfo()
                ->setIp("127.0.0.1")
                ->setLastUpdated(new \DateTimeImmutable()));

        $service = new IPInfoChecker(
            $this->createMock(IPStackService::class),
            $mockIPRepository,
            $this->createMock(\Doctrine\ORM\EntityManagerInterface::class)
        );

        $ipInfo = $service->checkIpInfo("127.0.0.1");

        $this->assertEquals("127.0.0.1", $ipInfo->getIp());
    }


    public function testIpCallsEndpointWithOldData(): void
    {

        $mockIPRepository = $this->createMock(\App\Repository\SavedIPInfoRepository::class);
        $mockIPRepository->method('findOneByIp')
            ->willReturn(new SavedIPInfo()
                ->setIp("127.0.0.1")
                ->setLastUpdated(new \DateTimeImmutable('-2 days')));

        $now = new \DateTimeImmutable();

        $ipStackService = $this->createIPStackService();
        $mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $mockEntityManager->expects($this->once())
            ->method('persist');

        $service = new IPInfoChecker(
            $ipStackService,
            $mockIPRepository,
            $mockEntityManager
        );

        $ipInfo = $service->checkIpInfo("127.0.0.1");

        $this->assertEquals("127.0.0.1", $ipInfo->getIp());
    }


    private function createIPStackService(): IPStackService
    {
        $response = new MockResponse('{"longitude": -122.07431030273438, "latitude": 37.38801956176758, "city": "Mountain View", "region_name": "California", "region_code": "CA", "country_code": "US", "continent_code": "NA", "type": "ipv4", "ip": "8.8.8.8"}');
        $client = new MockHttpClient([$response]);
        $logger = new NullLogger();

        return new IPStackService($client, $logger, "API_KEY");
    }
}
