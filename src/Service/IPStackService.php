<?php

namespace App\Service;

use App\DTO\IPStackDTO;
use App\Entity\SavedIP;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IPStackService
{

    private static string $IPSTACK_BASE = "https://api.ipstack.com/";
    private static string $IP_FIELDS = 'ip,type,continent_code,country_code,region_code,city,latitude,longitude';

    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
        #[Autowire('%env(IPSTACK_API_KEY)%')] private string $ipstackAccessKey
    ) {}


    public function fetchIpInfo(string $ip): IPStackDTO
    {
        $url = self::$IPSTACK_BASE . $ip;

        $response = $this->client->request(
            'GET',
            $url,
            ['query' => [
                'access_key' => $this->ipstackAccessKey,
                'fields' => self::$IP_FIELDS,
                'output' => 'json'
            ]]

        );

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('IPStack API request failed', [
                'ip' => $ip,
                'status_code' => $response->getStatusCode(),
                'response' => $response->getContent(false)
            ]);
            throw new \RuntimeException('Failed to fetch IP information from IPStack API');
        }
        $json_data = $response->getContent();

        $decoded = json_decode($json_data, true);
        if ($decoded === null || (isset($decoded['success']) && $decoded['success'] === 'false')) {
            $this->logger->error('IPStack API returned an error', [
                'ip' => $ip,
                'response' => $json_data
            ]);
            throw new \RuntimeException('IPStack API returned an error for IP: ' . $ip);
        }


        $dto = new IPStackDTO();
        $dto->setIp($decoded['ip'] );
        $dto->setType($decoded['type'] );
        $dto->setContinentCode($decoded['continent_code'] );
        $dto->setCountryCode($decoded['country_code'] );
        $dto->setRegionCode($decoded['region_code'] );
        $dto->setCity($decoded['city'] );
        $dto->setLatitude($decoded['latitude'] );
        $dto->setLongitude($decoded['longitude'] );

        return $dto;
}
}