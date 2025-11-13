<?php

namespace App\Tests\Service;

use App\Entity\BlockedIP;
use App\Repository\BlockedIPRepository;
use App\Repository\SavedIPInfoRepository;
use App\Service\BlacklistService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class BlacklistServiceTest extends TestCase
{

    public function testBlacklistSaved()
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist');
        $entityManager->expects($this->once())
            ->method('flush');


        $blockedIPRepository = $this->createMock(BlockedIPRepository::class);
        $blockedIPRepository->expects($this->once())
            ->method('findOneByIp')
            ->willReturn(null);
        $savedIPInfoRepository = $this->createMock(SavedIPInfoRepository::class);

        $blacklistService = new BlacklistService(
            $entityManager,
            $blockedIPRepository,
            $savedIPInfoRepository
        );

        $blacklistService->blacklistIP('127.0.0.1');
    }

    public function testUnblockIPFound()
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove');
        $entityManager->expects($this->once())
            ->method('flush');
        $blockedIPRepository = $this->createMock(BlockedIPRepository::class);
        $blockedIPRepository->expects($this->once())
            ->method('findOneByIp')
            ->willReturn(new BlockedIP());
        $savedIPInfoRepository = $this->createMock(SavedIPInfoRepository::class);

        $blacklistService = new BlacklistService(
            $entityManager,
            $blockedIPRepository,
            $savedIPInfoRepository
        );

        $result = $blacklistService->unblockIP('127.0.0.1');
        $this->assertTrue($result);
    }

    public function testUnblockIPNotFound()
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())
            ->method('remove');
        $entityManager->expects($this->never())
            ->method('flush');
        $blockedIPRepository = $this->createMock(BlockedIPRepository::class);
        $blockedIPRepository->expects($this->once())
            ->method('findOneByIp')
            ->willReturn(null);
        $savedIPInfoRepository = $this->createMock(SavedIPInfoRepository::class);

        $blacklistService = new BlacklistService(
            $entityManager,
            $blockedIPRepository,
            $savedIPInfoRepository
        );

        $result = $blacklistService->unblockIP('127.0.0.1');
        $this->assertFalse($result);
    }
}
