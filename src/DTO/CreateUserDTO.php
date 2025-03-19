<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserDTO
{
    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide.')]
    public string $prenom;

    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide.')]
    public string $nom;

    #[Assert\NotBlank(message: "L'email ne peut pas être vide")]
    #[Assert\Email(
        message: "L'email {{ value }} n'est pas valide.",
    )]
    public string $email;

    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins 8 caractères.'
    )]
    public ?string $password = null;
    #[Assert\NotBlank(message: 'Le numéro de téléphone ne peut pas être vide.')]
    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'Le numéro de téléphone doit contenir exactement 10 chiffres.'
    )]
    public string $telephone;
    public ?string $adresse = null;
    public ?string $cp = null;
    public ?string $commune = null;
}
