<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AssignWeekDTO
{
    #[Assert\NotBlank(message: 'Une semaine type doit être sélectionnée.')]
    #[Assert\Type(type: 'integer', message: "L'ID du modèle de semaine doit être un entier.")]
    public int $weekTypeId;

    #[Assert\NotBlank(message: 'Une semaine du calendrier doit être sélectionnée.')]
    public string $dateMonday;

    /**
     * @var CoursSnapshotItemDTO[]
     */
    #[Assert\NotBlank(
        message: 'Le snapshot des cours assignés ne peut pas être vide.',
        groups: ['not_empty_snapshot']
    )]
    #[Assert\Type(type: 'array', message: 'Le snapshot des cours assignés doit être un tableau.')]
    #[Assert\Valid]
    public array $coursList;
}
