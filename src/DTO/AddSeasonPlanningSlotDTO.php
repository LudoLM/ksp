<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AddSeasonPlanningSlotDTO
{
    #[Assert\NotNull(message: 'daySelected est requis')]
    #[Assert\Range(notInRangeMessage: 'daySelected doit être compris entre 0 (dimanche) et 6 (samedi)', min: 0, max: 6)]
    public int $daySelected;

    #[Assert\NotBlank(message: 'timeSelected est requis')]
    public string $timeSelected;

    #[Assert\NotBlank(message: 'typeCoursId est requis')]
    #[Assert\Positive(message: 'typeCoursId doit être positif')]
    public int $typeCoursId;
}
