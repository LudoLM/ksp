<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeasonPlanningSlotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

// Pas de contrainte d'unicité (planning, jour, heure) au niveau base : un index
// UNIQUE sur une colonne TIME fait planter ce build MySQL (SIGSEGV reproductible,
// isolé en diagnostic — voir docs/superpowers/plans/2026-09-02-season-planning.md).
// L'unicité est garantie côté applicatif par AddSlotService::addSlot(), qui
// cherche le créneau existant avant d'en créer un nouveau.
#[ORM\Entity(repositoryClass: SeasonPlanningSlotRepository::class)]
class SeasonPlanningSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['season_planning:index', 'wishes_form:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: SeasonPlanning::class)]
    #[ORM\JoinColumn(nullable: false)]
    private SeasonPlanning $seasonPlanning;

    #[ORM\Column]
    #[Groups(['season_planning:index'])]
    private int $daySelected;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['season_planning:index'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i:s'])]
    private \DateTimeInterface $timeSelected;

    /**
     * Cours possibles sur ce créneau : un seul = fixe, plusieurs = alternance
     * d'une semaine à l'autre. Ce n'est jamais un choix pour le membre — il
     * s'inscrit au créneau (jour/heure), pas à l'un de ces cours en particulier.
     *
     * @var Collection<int, TypeCours>
     */
    #[ORM\ManyToMany(targetEntity: TypeCours::class)]
    #[ORM\JoinTable(name: 'season_planning_slot_type_cours')]
    #[Groups(['season_planning:index'])]
    private Collection $typeCoursOptions;

    public function __construct()
    {
        $this->typeCoursOptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeasonPlanning(): SeasonPlanning
    {
        return $this->seasonPlanning;
    }

    public function setSeasonPlanning(SeasonPlanning $seasonPlanning): static
    {
        $this->seasonPlanning = $seasonPlanning;

        return $this;
    }

    public function getDaySelected(): int
    {
        return $this->daySelected;
    }

    public function setDaySelected(int $daySelected): static
    {
        $this->daySelected = $daySelected;

        return $this;
    }

    public function getTimeSelected(): \DateTimeInterface
    {
        return $this->timeSelected;
    }

    public function setTimeSelected(\DateTimeInterface $timeSelected): static
    {
        $this->timeSelected = $timeSelected;

        return $this;
    }

    /**
     * @return Collection<int, TypeCours>
     */
    public function getTypeCoursOptions(): Collection
    {
        return $this->typeCoursOptions;
    }

    public function addTypeCoursOption(TypeCours $typeCours): static
    {
        if (!$this->typeCoursOptions->contains($typeCours)) {
            $this->typeCoursOptions->add($typeCours);
        }

        return $this;
    }

    public function removeTypeCoursOption(TypeCours $typeCours): static
    {
        $this->typeCoursOptions->removeElement($typeCours);

        return $this;
    }
}
