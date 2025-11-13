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

    /**
     * Fetch IP information from IPStack API ( Bulk endpoint )
     * @param array $ips
     * @return array<IPStackDTO>
     */
    public function fetchBulkIpInfo(array $ips): array
    {
        if (count($ips) === 0) {
            return [];
        }

        if (count($ips) > 50) {
            throw new \InvalidArgumentException('max 50 IPs allowed for bulk request');
        }

        $ipList = implode(',', $ips);
        $url = self::$IPSTACK_BASE . $ipList;

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
            $this->logger->error('IPStack API bulk request failed', [
                'ips' => $ips,
                'status_code' => $response->getStatusCode(),
                'response' => $response->getContent(false)
            ]);
            throw new \RuntimeException('Failed to fetch IP information from IPStack API');
        }
        $json_data = $response->getContent();


        $decoded = json_decode($json_data, true);

        if ($decoded === null) {
            $this->logger->error('IPStack API bulk request returned invalid JSON', [
                'ips' => $ips,
                'response' => $json_data
            ]);
            throw new \RuntimeException('IPStack API returned invalid JSON for IPs: ' . implode(',', $ips));
        }

        if (isset($decoded['success']) && $decoded['success'] === 'false') {
            $this->logger->error('IPStack API bulk request returned an error', [
                'ips' => $ips,
                'response' => $json_data
            ]);
            throw new \RuntimeException('IPStack API returned an error for IPs: ' . implode(',', $ips));
        }

        $dtos = [];
        foreach ($decoded as $ipData) {

            $dto = new IPStackDTO();
            $dto->setIp($ipData['ip']);
            $dto->setType($ipData['type']);
            $dto->setContinentCode($ipData['continent_code']);
            $dto->setCountryCode($ipData['country_code']);
            $dto->setRegionCode($ipData['region_code']);
            $dto->setCity($ipData['city']);
            $dto->setLatitude($ipData['latitude']);
            $dto->setLongitude($ipData['longitude']);

            $dtos[] = $dto;
        }
        return $dtos;
    }




    /**
     * Call IPStack API to fetch info on IP address
     * 
     * @param string $ip
     * @return IPStackDTO
     */
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
        $dto->setIp($decoded['ip']);
        $dto->setType($decoded['type']);
        $dto->setContinentCode($decoded['continent_code']);
        $dto->setCountryCode($decoded['country_code']);
        $dto->setRegionCode($decoded['region_code']);
        $dto->setCity($decoded['city']);
        $dto->setLatitude($decoded['latitude']);
        $dto->setLongitude($decoded['longitude']);

        return $dto;
    }
}
