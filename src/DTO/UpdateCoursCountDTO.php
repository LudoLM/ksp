<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateCoursCountDTO
{
    #[Assert\NotNull(message: 'Le nombre de cours ne peut pas être nul.')]
    #[Assert\PositiveOrZero(message: 'Le nombre de cours doit être un entier positif ou 0.')]
    public int $nombreCours;
}
