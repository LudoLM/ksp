<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserDTO
{
    public ?string $prenom = null;

    public ?string $nom = null;

    #[Assert\Email(
        message: "L'email {{ value }} n'est pas valide.",
    )]
    public ?string $email = null;

    public ?string $token = null;

    #[Assert\NotBlank(message: 'Le mot de passe est requis.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins 8 caractères.'
    )]
    public ?string $password = null;
    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'Le numéro de téléphone doit contenir exactement 10 chiffres.'
    )]
    public ?string $telephone = null;
    public ?string $adresse = null;
    public ?string $cp = null;
    public ?string $commune = null;
}
