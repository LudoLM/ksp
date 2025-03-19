<?php

namespace App\DTO;

class SuperLightCoursDTO
{
    public function __construct(
        private readonly int $id,
        private readonly \DateTime $dateCours,
        private readonly int $duree,
        private int $statusCours,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDateCours(): \DateTime
    {
        return $this->dateCours;
    }

    public function getDuree(): int
    {
        return $this->duree;
    }

    public function getStatusCours(): int
    {
        return $this->statusCours;
    }

    public function setStatusCours(int $statusCours): self
    {
        $this->statusCours = $statusCours;

        return $this;
    }
}
