<?php

namespace App\Entity;

use App\Repository\ConseilRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConseilRepository::class)]
class Conseil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?TypeConseil $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;


    #[ORM\ManyToOne(targetEntity: Salle::class, inversedBy: 'conseils')]
    private ?Salle $salle;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    #[ORM\Column(nullable: true)]
    private ?int $valeur = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?TypeConseil
    {
        return $this->type;
    }

    public function setType(TypeConseil $type): static
    {
        $this->type = $type;

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


    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(Salle $salle): static
    {
        $this->salle = $salle;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getValeur(): ?int
    {
        return $this->valeur;
    }

    public function setValeur(?int $valeur): static
    {
        $this->valeur = $valeur;

        return $this;
    }
}
