<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'salles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Etage $etage = null;
    #[ORM\OneToOne(mappedBy: 'salle', cascade: ['persist', 'remove'])]
    private ?DetailsSalle $detailsSalle = null;

    /**
     * @var Collection<int, Plan>
     */
    #[ORM\OneToMany(targetEntity: Plan::class, mappedBy: 'salle', orphanRemoval: true)]
    private Collection $plans;

    /**
     * @var Collection<int, Note>
     */
    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'salle', orphanRemoval: true)]
    private Collection $notes;
     
    /** 
     * @var Collection<int, Conseil>
     */
    #[ORM\OneToMany(targetEntity: Conseil::class, mappedBy: 'salle', cascade: ['remove'])]
    private Collection $conseils;

    public function __construct()
    {
        $this->historiqueSalles = new ArrayCollection();
        $this->plans = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->conseils = new ArrayCollection();
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

    public function getEtage(): ?Etage
    {
        return $this->etage;
    }

    public function setEtage(?Etage $etage): static
    {
        $this->etage = $etage;

        return $this;
    }
    public function getDetailsSalle(): ?DetailsSalle
    {
        return $this->detailsSalle;
    }

    public function setDetailsSalle(DetailsSalle $detailsSalle): static
    {
        // set the owning side of the relation if necessary
        if ($detailsSalle->getSalle() !== $this) {
            $detailsSalle->setSalle($this);
        }

        $this->detailsSalle = $detailsSalle;

        return $this;
    }

    /**
     * @return Collection<int, Plan>
     */
    public function getPlans(): Collection
    {
        return $this->plans;
    }

    public function addPlan(Plan $plan): static
    {
        if (!$this->plans->contains($plan)) {
            $this->plans->add($plan);
            $plan->setSalle($this);
        }

        return $this;
    }

    public function removePlan(Plan $plan): static
    {
        if ($this->plans->removeElement($plan)) {
            // set the owning side to null (unless already changed)
            if ($plan->getSalle() === $this) {
                $plan->setSalle(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setSalle($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Conseil>
     */
    public function getConseils(): Collection
    {
        return $this->conseils;
    }

    public function addConseil(Conseil $conseil): static
    {
        if (!$this->conseils->contains($conseil)) {
            $this->conseils->add($conseil);
            $conseil->setSalle($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getSalle() === $this) {
                $note->setSalle(null);
            }
        }

        return $this;
    }

    public function removeConseil(Conseil $conseil): static
    {
        if ($this->conseils->removeElement($conseil)) {
            $conseil->removeSalle($this);
        }

        return $this;
    }
}
