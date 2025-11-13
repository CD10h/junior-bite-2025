<?php

namespace App\Entity;

use App\Repository\BlockedIPRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: BlockedIPRepository::class)]
class BlockedIP
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 39)]
    private ?string $ip = null;

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
}
