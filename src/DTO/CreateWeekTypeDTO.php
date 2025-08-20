<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateWeekTypeDTO
{
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide.')]
    public string $name;

    /**
     * @var CoursSnapshotItemDTO[]
     */
    #[Assert\NotBlank(
        message: 'Le snapshot des cours assignés ne peut pas être vide.',
        groups: ['not_empty_snapshot']
    )]
    #[Assert\Type(type: 'array', message: 'Le snapshot des cours assignés doit être un tableau.')]
    #[Assert\Valid]
    public array $weekTypeArray;
}
