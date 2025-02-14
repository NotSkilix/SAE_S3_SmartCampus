<?php

namespace App\Entity;

use App\Repository\CapteurRepository;
use App\Entity\Etat;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CapteurRepository::class)]
class Capteur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $valeur = null;

    #[ORM\Column(type: 'string', enumType: Type::class)]
    private ?Type $type = null;

    #[ORM\ManyToOne(inversedBy: 'capteurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SystemAcquisition $SA = null;

    #[ORM\Column(nullable: true)]
    private ?int $idAPI = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $date = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeur(): ?float
    {
        return $this->valeur;
    }

    public function setValeur(float $valeur): self
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(Type $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSA(): ?SystemAcquisition
    {
        return $this->SA;
    }

    public function setSA(?SystemAcquisition $SA): static
    {
        $this->SA = $SA;

        return $this;
    }

    public function getIdAPI(): ?int
    {
        return $this->idAPI;
    }

    public function setIdAPI(?int $idAPI): static
    {
        $this->idAPI = $idAPI;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }
}
