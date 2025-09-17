<?php

namespace App\Entity;

use App\Repository\StatusCoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: StatusCoursRepository::class)]
class StatusCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cours:index', 'cours:detail', 'user:profile', 'status_cours:index'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['cours:index', 'cours:detail', 'user:profile', 'status_cours:index'])]
    private string $libelle;

    /**
     * @var Collection<int, Cours>
     */
    #[ORM\OneToMany(mappedBy: 'statusCours', targetEntity: Cours::class, orphanRemoval: true)]
    private Collection $cours;

    public function __construct()
    {
        $this->cours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Cours>
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }
}
