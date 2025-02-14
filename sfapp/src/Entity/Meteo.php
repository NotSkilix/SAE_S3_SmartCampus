<?php

namespace App\Entity;

use App\Repository\MeteoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MeteoRepository::class)]
class Meteo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $temp = null;

    #[ORM\Column(nullable: true)]
    private ?float $hum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemp(): ?float
    {
        return $this->temp;
    }

    public function setTemp(?float $temp): static
    {
        $this->temp = $temp;

        return $this;
    }

    public function getHum(): ?float
    {
        return $this->hum;
    }

    public function setHum(?float $hum): static
    {
        $this->hum = $hum;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }
}
