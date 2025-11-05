<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserIdsToRemoveDTO
{
    #[Assert\NotBlank]
    #[Assert\All([
        new Assert\Type('integer'),
        new Assert\Positive(),
    ])]
    public array $usersChecked;
}
