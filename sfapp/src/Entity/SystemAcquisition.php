<?php

namespace App\Entity;

use App\Repository\SystemAcquisitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SystemAcquisitionRepository::class)]
class SystemAcquisition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', enumType: Etat::class)]
    private ?Etat $etat = null;

    /**
     * @var Collection<int, Capteur>
     */
    #[ORM\OneToMany(targetEntity: Capteur::class, mappedBy: 'SA', orphanRemoval: true)]
    private Collection $capteurs;

    #[ORM\OneToOne(mappedBy: 'SA', cascade: ['persist', 'remove'])]
    private ?Plan $plan = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDerniereModification = null;

    public function __construct()
    {
        $this->capteurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Capteur>
     */
    public function getCapteurs(): Collection
    {
        return $this->capteurs;
    }

    public function addCapteur(Capteur $capteur): static
    {
        if (!$this->capteurs->contains($capteur)) {
            $this->capteurs->add($capteur);
            $capteur->setSA($this);
        }

        return $this;
    }

    public function removeCapteur(Capteur $capteur): static
    {
        if ($this->capteurs->removeElement($capteur)) {
            // set the owning side to null (unless already changed)
            if ($capteur->getSA() === $this) {
                $capteur->setSA(null);
            }
        }

        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(Plan $plan): static
    {
        // set the owning side of the relation if necessary
        if ($plan->getSA() !== $this) {
            $plan->setSA($this);
        }

        $this->plan = $plan;

        return $this;
    }

    public function removePlan(): static
    {
        $this->plan = null;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateDerniereModification(): ?\DateTimeInterface
    {
        return $this->dateDerniereModification;
    }

    public function setDateDerniereModification(?\DateTimeInterface $dateDerniereModification): static
    {
        $this->dateDerniereModification = $dateDerniereModification;

        return $this;
    }
}
