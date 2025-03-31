<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordDTO
{
    public function __construct(public string $userId, #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins 8 caractères.'
    )]
        public string $password, public string $token)
    {
    }
}
