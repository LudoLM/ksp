<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCoursDTO
{
    #[Assert\NotBlank(message: 'La durée ne peut pas être vide.')]
    #[Assert\GreaterThanOrEqual(1, message: 'La durée du cours doit être positive')]
    public ?int $dureeCours = null;

    #[Assert\NotBlank(message: 'La date du cours ne peut pas être vide.')]
    #[Assert\GreaterThanOrEqual('+1 day', message: "Le cours ne peut pas être créé sans un délai d'un jour.")]
    public ?\DateTimeInterface $dateCours = null;

    public ?string $specialNote = null;
    #[Assert\NotBlank(message: "Le nombre d'inscription max ne peut pas être vide.")]
    #[Assert\GreaterThanOrEqual(1, message: "Le nombre d'inscriptions max doit être positive.")]
    public ?int $nbInscriptionMax = null;

    #[Assert\NotBlank(message: 'Le type de cours ne peut pas être vide.')]
    public ?int $typeCours = null;

    public function getDureeCours(): ?int
    {
        return $this->dureeCours;
    }

    public function getDateCours(): ?\DateTimeInterface
    {
        return $this->dateCours;
    }

    public function getSpecialNote(): ?string
    {
        return $this->specialNote;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function getTypeCours(): ?int
    {
        return $this->typeCours;
    }
}
