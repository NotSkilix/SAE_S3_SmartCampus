<?php

namespace App\Entity;

use App\Repository\BatimentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BatimentRepository::class)]
class Batiment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25, unique: true)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Etage>
     */
    #[ORM\OneToMany(targetEntity: Etage::class, mappedBy: 'batiment', orphanRemoval: true)]
    private Collection $etages;

    /**
     * @var Collection<int, Note>
     */
    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'batiment')]
    private Collection $notes;

    public function __construct()
    {
        $this->etages = new ArrayCollection();
        $this->notes = new ArrayCollection();
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

    /**
     * @return Collection<int, Etage>
     */
    public function getEtages(): Collection
    {
        return $this->etages;
    }

    public function addEtage(Etage $etage): static
    {
        if (!$this->etages->contains($etage)) {
            $this->etages->add($etage);
            $etage->setBatiment($this);
        }

        return $this;
    }

    public function removeEtage(Etage $etage): static
    {
        if ($this->etages->removeElement($etage)) {
            // set the owning side to null (unless already changed)
            if ($etage->getBatiment() === $this) {
                $etage->setBatiment(null);
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
            $note->setBatiment($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getBatiment() === $this) {
                $note->setBatiment(null);
            }
        }

        return $this;
    }
}
