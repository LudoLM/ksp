<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class EditUserDTO
{
    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide.')]
    public string $prenom;

    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide.')]
    public string $nom;

    #[Assert\NotBlank(message: 'Le numéro de téléphone ne doit pas être vide.')]
    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'Le numéro de téléphone doit contenir exactement 10 chiffres.'
    )]
    public string $telephone;
    public ?string $adresse = null;
    public ?string $cp = null;
    public ?string $commune = null;
}
