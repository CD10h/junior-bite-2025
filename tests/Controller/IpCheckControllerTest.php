<?php

namespace App\Tests\Controller;

use App\Controller\IpCheckController;
use App\Entity\SavedIPInfo;
use App\Service\BlacklistService;
use App\Service\IPInfoChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class IpCheckControllerTest extends KernelTestCase
{

    public function testCheckIPreturnsInfoForIP(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $ipInfoChecker = $this->createMock(IPInfoChecker::class);
        $ipInfoChecker->expects($this->once())
            ->method('checkIpInfo')
            ->with('127.0.0.1')
            ->willReturn(new SavedIPInfo()
                ->setIp('127.0.0.1'));
        $container->set(IPInfoChecker::class, $ipInfoChecker);

        $blacklistService = $this->createMock(BlacklistService::class);
        $container->set(BlacklistService::class, $blacklistService);

        $controller = $container->get(IpCheckController::class);

        $result = $controller->checkIP('127.0.0.1');

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertStringContainsString('"ip":"127.0.0.1"', $result->getContent());
    }


    public function testCheckIPReturns400ForInvalidIP(): void
    {
        self::bootKernel();
        $container = static::getContainer();


        $ipInfoChecker = $this->createMock(IPInfoChecker::class);
        $container->set(IPInfoChecker::class, $ipInfoChecker);

        $controller = $container->get(IpCheckController::class);

        $result = $controller->checkIP('invalid-ip');

        $this->assertEquals(400, $result->getStatusCode());
        $this->assertStringContainsString('Invalid IP address', $result->getContent());
    }

    public function testCheckIPReturnsBlacklistedForBlacklistedIP(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $ipInfoChecker = $this->createMock(IPInfoChecker::class);
        $container->set(IPInfoChecker::class, $ipInfoChecker);
        $blacklistService = $this->createMock(BlacklistService::class);
        $blacklistService->expects($this->once())
            ->method('isBlacklisted')
            ->with('127.0.0.1')
            ->willReturn(true);
        $container->set(BlacklistService::class, $blacklistService);

        $controller = $container->get(IpCheckController::class);
        $result = $controller->checkIP('127.0.0.1');

        $this->assertEquals(403, $result->getStatusCode());
        $this->assertStringContainsString('IP is blacklisted', $result->getContent());
    }
}
