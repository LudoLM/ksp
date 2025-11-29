<?php

namespace App\Entity\Abstracts;

use App\Entity\TypeCours;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\MappedSuperclass]
abstract class Coursbase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cours:index', 'cours:detail', 'user:profile', 'cours_filling:index', 'week_type:index'])]
    protected ?int $id = null;

    #[ORM\Column]
    #[Groups(['cours:index', 'cours:detail', 'week_type:index'])]
    protected int $duree;

    #[ORM\Column(nullable: false)]
    #[Groups(['cours:index', 'cours:detail', 'cours_filling:index', 'week_type:index'])]
    protected int $nbInscriptionMax;

    #[ORM\Column(nullable: false)]
    #[Groups(['cours:index', 'cours:detail', 'cours_filling:index', 'week_type:index'])]
    protected bool $hasPriority;

    #[ORM\Column(nullable: false)]
    #[Groups(['cours:index', 'cours:detail', 'cours_filling:index', 'week_type:index'])]
    protected bool $hasLimitOfOneCoursPerWeek;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cours:index', 'cours:detail', 'user:profile', 'week_type:index', 'usersCours:read'])]
    protected TypeCours $typeCours;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['cours:index', 'cours:detail', 'week_type:index'])]
    private ?string $specialNote = null;

    // Getters and Setters for each property can be added here

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): void
    {
        $this->duree = $duree;
    }

    public function getNbInscriptionMax(): int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(int $nbInscriptionMax): void
    {
        $this->nbInscriptionMax = $nbInscriptionMax;
    }

    public function getTypeCours(): TypeCours
    {
        return $this->typeCours;
    }

    public function setTypeCours(TypeCours $typeCours): void
    {
        $this->typeCours = $typeCours;
    }

    public function hasPriority(): bool
    {
        return $this->hasPriority;
    }

    public function setHasPriority(bool $hasPriority): void
    {
        $this->hasPriority = $hasPriority;
    }

    public function hasLimitOfOneCoursPerWeek(): bool
    {
        return $this->hasLimitOfOneCoursPerWeek;
    }

    public function setHasLimitOfOneCoursPerWeek(bool $hasLimitOfOneCoursPerWeek): void
    {
        $this->hasLimitOfOneCoursPerWeek = $hasLimitOfOneCoursPerWeek;
    }

    public function getSpecialNote(): string
    {
        return $this->specialNote;
    }

    public function setSpecialNote(string $specialNote): void
    {
        $this->specialNote = $specialNote;
    }
}
