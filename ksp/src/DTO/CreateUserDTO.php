<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
class CreateUserDTO
{
    public ?string $prenom;
    public ?string $nom;
    public ?string $email;
    public ?string $password;
    #[Assert\NotBlank(message: "Le numéro de téléphone ne doit pas être vide.")]
    #[Assert\Regex(
        pattern: "/^[0-9]{10}$/",
        message: "Le numéro de téléphone doit contenir exactement 10 chiffres."
    )]
    public ?string $telephone;
    public ?string $adresse;
    public ?string $cp;
    public ?string $commune;
}