<?php

namespace App\Entity;

use App\Repository\SavedIPRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

#[HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: SavedIPRepository::class)]
class SavedIP
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 39, unique: true)]
    private ?string $ip = null;

    #[ORM\Column(length: 4)]
    private ?string $type = null;

    #[ORM\Column(length: 2)]
    private ?string $continent_code = null;

    #[ORM\Column(length: 2)]
    private ?string $country_code = null;

    #[ORM\Column(length: 10)]
    private ?string $region_code = null;

    #[ORM\Column(length: 20)]
    private ?string $city = null;

    #[ORM\Column]
    private ?float $latitude = null;

    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastUpdated = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getContinentCode(): ?string
    {
        return $this->continent_code;
    }

    public function setContinentCode(string $continent_code): static
    {
        $this->continent_code = $continent_code;

        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    public function setCountryCode(string $country_code): static
    {
        $this->country_code = $country_code;

        return $this;
    }

    public function getRegionCode(): ?string
    {
        return $this->region_code;
    }

    public function setRegionCode(string $region_code): static
    {
        $this->region_code = $region_code;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function setLastUpdated(\DateTimeImmutable $lastUpdated): static
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getLastUpdated(): ?\DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    #[PreUpdate]
    #[PrePersist]
    public function lastUpdatedChange() {
        $this->lastUpdated = new \DateTimeImmutable();
    }

}
