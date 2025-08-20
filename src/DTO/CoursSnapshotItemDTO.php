<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CoursSnapshotItemDTO
{
    #[Assert\NotBlank(message: 'Le jour de la semaine doit être spécifié.')]
    #[Assert\Type(type: 'integer', message: 'Le jour de la semaine doit être un entier.')]
    #[Assert\Range(notInRangeMessage: 'Le jour de la semaine doit être entre {{ min }} et {{ max }}.', min: 0, max: 6)]
    public int $daySelected;

    #[Assert\NotBlank(message: "L'heure du cours doit être spécifiée.")]
    #[Assert\Type(type: 'string', message: "L'heure du cours doit être une chaîne de caractères.")]
    #[Assert\Regex(pattern: '/^([0-1]?\d|2[0-3]):[0-5]\d$/', message: "L'heure du cours doit être au format HH:MM.")]
    public string $timeSelected;

    #[Assert\NotBlank(message: 'La durée du cours doit être spécifiée.')]
    #[Assert\Type(type: 'integer', message: 'La durée du cours doit être un entier.')]
    #[Assert\Positive(message: 'La durée du cours doit être positive.')]
    public int $duree;

    #[Assert\NotBlank(message: "Le nombre maximal d'inscriptions doit être spécifié.")]
    #[Assert\Type(type: 'integer', message: "Le nombre maximal d'inscriptions doit être un entier.")]
    #[Assert\PositiveOrZero(message: "Le nombre maximal d'inscriptions doit être positif ou zéro.")]
    public int $nbInscriptionMax;

    #[Assert\Type(type: 'string', message: 'La note spéciale doit être une chaîne de caractères.')]
    public ?string $specialNote = null;

    #[Assert\NotNull(message: 'La priorité doit être spécifiée.')]
    #[Assert\Type(type: 'bool', message: 'La priorité doit être un booléen.')]
    public bool $hasPriority;

    #[Assert\NotNull(message: 'La limite de cours par semaine doit être spécifiée.')]
    #[Assert\Type(type: 'bool', message: "La limite d'un cours par semaine doit être un booléen.")]
    public bool $hasLimitOfOneCoursPerWeek;

    #[Assert\NotBlank(message: 'Le type de cours doit être spécifié.')]
    #[Assert\Type(type: 'array', message: 'Le type de cours doit être un objet (tableau associatif).')]
    public array $typeCours;
}
