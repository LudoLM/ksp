<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeasonPlanningRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeasonPlanningRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_season_planning_saison', columns: ['saison'])]
class SeasonPlanning
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $saison;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSaison(): string
    {
        return $this->saison;
    }

    public function setSaison(string $saison): static
    {
        $this->saison = $saison;

        return $this;
    }
}
