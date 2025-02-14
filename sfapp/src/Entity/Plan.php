<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plans')]
    private ?Salle $salle = null;

    #[ORM\OneToOne(inversedBy: 'plan', cascade: ['persist', 'remove'])]
    private ?SystemAcquisition $SA = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_association = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_desassociation = null;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;

        return $this;
    }

    public function getSA(): ?SystemAcquisition
    {
        return $this->SA;
    }

    public function setSA(SystemAcquisition $SA): static
    {
        $this->SA = $SA;

        return $this;
    }

    public function getDateAssociation(): ?\DateTimeInterface
    {
        return $this->date_association;
    }

    public function setDateAssociation(\DateTimeInterface $date_association): static
    {
        $this->date_association = $date_association;

        return $this;
    }

    public function getDateDesassociation(): ?\DateTimeInterface
    {
        return $this->date_desassociation;
    }

    public function setDateDesassociation(?\DateTimeInterface $date_desassociation): static
    {
        $this->date_desassociation = $date_desassociation;

        return $this;
    }
}
