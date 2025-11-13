<?php

namespace App\Tests\Controller;

use App\Controller\ApiController;
use App\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApiControllerTest extends KernelTestCase
{
    public function testHealthEndpoint(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $controller = $container->get(ApiController::class);

        $response = $controller->health();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('{"status":"ok"}', $response->getContent());
    }
}
