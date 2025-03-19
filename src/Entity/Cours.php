<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update', 'cours:update', 'user:detail', 'cours_filling:index'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update'])]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update', 'user:detail', 'cours_filling:index'])]
    private ?\DateTimeInterface $dateCours = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update'])]
    private ?string $specialNote = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update', 'cours_filling:index'])]
    private ?int $nbInscriptionMax = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update', 'user:detail'])]
    private TypeCours $typeCours;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update', 'user:detail'])]
    private StatusCours $statusCours;

    /**
     * @var Collection<int, UsersCours>
     */
    #[ORM\OneToMany(mappedBy: 'cours', targetEntity: UsersCours::class, orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update', 'cours_filling:index'])]
    private Collection $usersCours;

    public function __construct()
    {
        $this->usersCours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): void
    {
        $this->duree = $duree;
    }

    public function getDateCours(): ?\DateTimeInterface
    {
        return $this->dateCours;
    }

    public function setDateCours(?\DateTimeInterface $dateCours): void
    {
        $this->dateCours = $dateCours;
    }

    public function getSpecialNote(): ?string
    {
        return $this->specialNote;
    }

    public function setSpecialNote(?string $specialNote): void
    {
        $this->specialNote = $specialNote;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(?int $nbInscriptionMax): void
    {
        $this->nbInscriptionMax = $nbInscriptionMax;
    }

    public function getTypeCours(): ?TypeCours
    {
        return $this->typeCours;
    }

    public function setTypeCours(?TypeCours $typeCours): void
    {
        $this->typeCours = $typeCours;
    }

    public function getStatusCours(): ?StatusCours
    {
        return $this->statusCours;
    }

    public function setStatusCours(?StatusCours $statusCours): void
    {
        $this->statusCours = $statusCours;
    }

    /**
     * @return Collection<int, UsersCours>
     */
    public function getUsersCours(): Collection
    {
        return $this->usersCours;
    }

    public function addUsersCours(UsersCours $usersCours): self
    {
        if (!$this->usersCours->contains($usersCours)) {
            $this->usersCours->add($usersCours);
            $usersCours->setCours($this);
        }

        return $this;
    }

    public function removeUsersCours(UsersCours $usersCours): self
    {
        // set the owning side to null (unless already changed)
        if ($this->usersCours->removeElement($usersCours) && $usersCours->getCours() === $this) {
            $usersCours->setCours(null);
        }

        return $this;
    }
}
