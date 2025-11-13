<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\IPStackService;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class IPstackServiceTest extends TestCase
{

    public function testFetchIPHandlesApiError(): void
    {

        $response = new MockResponse('{"success":"false","error":{"code":102,"type":"mock_error", "info":"example info"}}', ['http_code' => 200]);
        $service = $this->createIPStackServiceWithResponse($response);


        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('IPStack API returned an error for IP: 8.8.8.8');

        $service->fetchIpInfo("8.8.8.8");
    }


    public function testFetchIPHandlesHttpError(): void
    {

        $response = new MockResponse('Internal Server Error', ['http_code' => 500]);
        $service = $this->createIPStackServiceWithResponse($response);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch IP information from IPStack API');

        $service->fetchIpInfo("8.8.8.8");
    }


    public function testFetchIPReturnsCorrectData(): void
    {

        $response = new MockResponse('{"longitude": -122.07431030273438, "latitude": 37.38801956176758, "city": "Mountain View", "region_name": "California", "region_code": "CA", "country_code": "US", "continent_code": "NA", "type": "ipv4", "ip": "8.8.8.8"}');
        $service = $this->createIPStackServiceWithResponse($response);

        $ipInfo = $service->fetchIpInfo("8.8.8.8");

        $this->assertEquals("ipv4", $ipInfo->getType());
        $this->assertEquals("NA", $ipInfo->getContinentCode());
        $this->assertEquals("US", $ipInfo->getCountryCode());
        $this->assertEquals("CA", $ipInfo->getRegionCode());
        $this->assertEquals("Mountain View", $ipInfo->getCity());
        $this->assertEquals(37.38801956176758, $ipInfo->getLatitude());
        $this->assertEquals(-122.07431030273438, $ipInfo->getLongitude());
    }


    private function createIPStackServiceWithResponse(MockResponse $response): IPStackService
    {
        $client = new MockHttpClient([$response]);
        $logger = new NullLogger();

        return new IPStackService($client, $logger, "API_KEY");
    }
}
