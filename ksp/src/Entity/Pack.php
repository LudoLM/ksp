<?php

namespace App\Entity;

use App\Repository\PackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PackRepository::class)]
class Pack
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:detail'])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Groups(['user:detail'])]
    #[ORM\Column]
    private ?float $tarif = null;


    #[ORM\Column]
    private ?int $nombreCours = null;

    /**
     * @var Collection<int, HistoriquePaiement>
     */
    #[ORM\OneToMany(mappedBy: 'pack', targetEntity: HistoriquePaiement::class)]
    private Collection $historiquePaiements;

    public function __construct()
    {
        $this->historiquePaiements = new ArrayCollection();
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

    public function getTarif(): ?float
    {
        return $this->tarif;
    }

    public function setTarif(float $tarif): static
    {
        $this->tarif = $tarif;

        return $this;
    }

    public function getNombreCours(): ?int
    {
        return $this->nombreCours;
    }

    public function setNombreCours(int $nombreCours): static
    {
        $this->nombreCours = $nombreCours;

        return $this;
    }

    /**
     * @return Collection<int, HistoriquePaiement>
     */
    public function getHistoriquePaiements(): Collection
    {
        return $this->historiquePaiements;
    }

    public function addHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if (!$this->historiquePaiements->contains($historiquePaiement)) {
            $this->historiquePaiements->add($historiquePaiement);
            $historiquePaiement->setPack($this);
        }

        return $this;
    }

    public function removeHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if ($this->historiquePaiements->removeElement($historiquePaiement)) {
            // set the owning side to null (unless already changed)
            if ($historiquePaiement->getPack() === $this) {
                $historiquePaiement->setPack(null);
            }
        }

        return $this;
    }
}
