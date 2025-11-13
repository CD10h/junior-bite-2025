<?php

namespace App\Tests\Controller;

use App\Controller\BlacklistController;
use App\Service\BlacklistService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;


class BlacklistControllerTest extends KernelTestCase
{

    public function testBlacklistIPfailsWithInvalidIP(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $controller = $container->get(BlacklistController::class);

        $request = new Request(content: json_encode(['ip' => 'invalid-ip']));


        $response = $controller->blacklistIP($request);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid IP address', $response->getContent());
    }

    public function testBlacklistIPsucceedsWithValidIP(): void
    {
        self::bootKernel();
        $container = static::getContainer();


        $blacklistService = $this->createMock(BlacklistService::class);
        $blacklistService->expects($this->once())
            ->method('blacklistIP')
            ->with('192.168.1.1');
        $container->set(BlacklistService::class, $blacklistService);

        $controller = $container->get(BlacklistController::class);


        $request = new Request(content: json_encode(['ip' => '192.168.1.1']));
        $response = $controller->blacklistIP($request);
        $this->assertEquals(200, $response->getStatusCode());
    }


    public function testUnblockIP404sWithInvalidIP(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $blacklistService = $this->createMock(BlacklistService::class);
        $container->set(BlacklistService::class, $blacklistService);


        $controller = $container->get(BlacklistController::class);

        $response = $controller->deleteBlacklistedIP('invalid-ip');
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Not found', $response->getContent());
    }

    public function testUnblockIPsucceedsWithValidIP(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $blacklistService = $this->createMock(BlacklistService::class);
        $blacklistService->expects($this->once())
            ->method('unblockIP')
            ->with('192.168.1.1')
            ->willReturn(true);
        $container->set(BlacklistService::class, $blacklistService);

        $controller = $container->get(BlacklistController::class);

        $response = $controller->deleteBlacklistedIP('192.168.1.1');
        $this->assertEquals(200, $response->getStatusCode());
    }


    public function testBulkBlacklistEmptyIsOk(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $blacklistService = $this->createMock(BlacklistService::class);
        $container->set(BlacklistService::class, $blacklistService);
        $controller = $container->get(BlacklistController::class);

        $request = new Request(content: json_encode(['ips' => []]));
        $response = $controller->bulkBlacklistIPs($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('{"status":"OK"}', $response->getContent());
    }


    public function testBulkBlacklistMixedValidAndInvalidIPs(): void
    {
        self::bootKernel();
        $container = static::getContainer();


        $ips = ['invalid-ip', '192.168.1.1'];
        $request = new Request(content: json_encode(['ips' => $ips]));

        $blacklistService = $this->createMock(BlacklistService::class);
        $blacklistService->expects($this->never())
            ->method('blacklistIP')
            ->with('192.168.1.1');
        $container->set(BlacklistService::class, $blacklistService);

        $controller = $container->get(BlacklistController::class);

        $response = $controller->bulkBlacklistIPs($request);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('IPs are invalid', $response->getContent());
        $this->assertStringContainsString('invalid-ip', $response->getContent());
        $this->assertStringNotContainsString('192.168.1.1', $response->getContent());
    }
}
