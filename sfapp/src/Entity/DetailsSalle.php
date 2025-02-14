<?php

namespace App\Entity;

use App\Repository\DetailsSalleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailsSalleRepository::class)]
class DetailsSalle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $superficie = null;

    #[ORM\Column(nullable: true)]
    private ?int $radiateur = null;

    #[ORM\Column(nullable: true)]
    private ?int $fenetre = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?Exposition $exposition = null;

    #[ORM\Column(nullable: true)]
    private ?int $porte = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?Frequentation $frequentation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_de_creation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_derniere_modification = null;

    #[ORM\OneToOne(inversedBy: 'detailsSalle', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $salle = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSuperficie(): ?int
    {
        return $this->superficie;
    }

    public function setSuperficie(?int $superficie): static
    {
        $this->superficie = $superficie;

        return $this;
    }

    public function getRadiateur(): ?int
    {
        return $this->radiateur;
    }

    public function setRadiateur(?int $radiateur): static
    {
        $this->radiateur = $radiateur;

        return $this;
    }

    public function getFenetre(): ?int
    {
        return $this->fenetre;
    }

    public function setFenetre(?int $fenetre): static
    {
        $this->fenetre = $fenetre;

        return $this;
    }

    public function getExposition(): ?Exposition
    {
        return $this->exposition;
    }

    public function setExposition(Exposition $exposition): static
    {
        $this->exposition = $exposition;

        return $this;
    }

    public function getPorte(): ?int
    {
        return $this->porte;
    }

    public function setPorte(?int $porte): static
    {
        $this->porte = $porte;

        return $this;
    }

    public function getFrequentation(): ?Frequentation
    {
        return $this->frequentation;
    }

    public function setFrequentation(?Frequentation $frequentation): static
    {
        $this->frequentation = $frequentation;

        return $this;
    }

    public function getDateDeCreation(): ?\DateTimeInterface
    {
        return $this->date_de_creation;
    }

    public function setDateDeCreation(\DateTimeInterface $date_de_creation): static
    {
        $this->date_de_creation = $date_de_creation;

        return $this;
    }

    public function getDateDerniereModification(): ?\DateTimeInterface
    {
        return $this->date_derniere_modification;
    }

    public function setDateDerniereModification(?\DateTimeInterface $date_derniere_modification): static
    {
        $this->date_derniere_modification = $date_derniere_modification;

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
}
