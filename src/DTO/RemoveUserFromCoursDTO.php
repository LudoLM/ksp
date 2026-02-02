<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RemoveUserFromCoursDTO
{
    #[Assert\NotBlank(message: 'coursId est requis')]
    #[Assert\Type(type: 'integer', message: 'coursId doit être un entier')]
    #[Assert\Positive(message: 'coursId doit être positif')]
    public int $coursId;

    #[Assert\NotNull(message: 'isOnWaitingList est requis')]
    #[Assert\Type(type: 'bool', message: 'isOnWaitingList doit être un booléen')]
    public bool $isOnWaitingList;
}
