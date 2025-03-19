<?php

namespace App\Entity;

use App\Repository\TypeCoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeCoursRepository::class)]
class TypeCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cours:index', 'cours:detail', 'type_cours:index'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['cours:index', 'cours:detail', 'type_cours:index', 'user:detail'])]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide.')]
    private string $libelle;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT)]
    #[Groups(['cours:index', 'cours:detail', 'type_cours:index'])]
    #[Assert\NotBlank(message: 'Le descriptif ne peut pas être vide.')]
    private string $descriptif;

    /**
     * @var Collection<int, Cours>
     */
    #[ORM\OneToMany(mappedBy: 'typeCours', targetEntity: Cours::class, orphanRemoval: true)]
    private Collection $cours;

    #[Groups(['cours:index', 'cours:detail', 'type_cours:index'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'image ne peut pas être vide.")]
    private string $thumbnail;

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

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDescriptif(): ?string
    {
        return $this->descriptif;
    }

    public function setDescriptif(string $descriptif): self
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    /**
     * @return Collection<int, Cours>
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }

    public function addCour(Cours $cour): self
    {
        if (!$this->cours->contains($cour)) {
            $this->cours->add($cour);
            $cour->setTypeCours($this);
        }

        return $this;
    }

    public function removeCour(Cours $cour): self
    {
        // set the owning side to null (unless already changed)
        if ($this->cours->removeElement($cour) && $cour->getTypeCours() === $this) {
            $cour->setTypeCours(null);
        }

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }
}
