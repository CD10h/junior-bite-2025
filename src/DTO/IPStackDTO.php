<?php

namespace App\DTO;


class IPStackDTO
{
    private string $ip;
    private string $type;
    private string $continentCode;
    private string $countryCode;
    private string $regionCode;
    private string $city;
    private float $latitude;
    private float $longitude;

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContinentCode(): string
    {
        return $this->continentCode;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getRegionCode(): string
    {
        return $this->regionCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }
    public function setType(string $type): void
    {
        $this->type = $type;
    }
    public function setContinentCode(string $continentCode): void
    {
        $this->continentCode = $continentCode;
    }
    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }
    public function setRegionCode(string $regionCode): void
    {
        $this->regionCode = $regionCode;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }
    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }
    public function setLongitude(float $longitude): void
    {
        $this->longitude = $longitude;
    }
}
