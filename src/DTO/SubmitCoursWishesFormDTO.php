<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\PaymentMethodEnum;
use Symfony\Component\Validator\Constraints as Assert;

class SubmitCoursWishesFormDTO
{
    #[Assert\NotBlank(message: "L'email est requis")]
    #[Assert\Email(message: "L'email n'est pas valide")]
    public string $email;

    public ?string $nom = null;

    public ?string $prenom = null;

    #[Assert\Regex(pattern: '/^\d{10}$/', message: 'Le numéro de téléphone doit contenir exactement 10 chiffres.')]
    public ?string $telephone = null;

    #[Assert\NotBlank(message: 'creneauPrimaireId est requis')]
    #[Assert\Positive(message: 'creneauPrimaireId doit être positif')]
    public int $creneauPrimaireId;

    #[Assert\Positive(message: 'creneauSecondaireId doit être positif')]
    public ?int $creneauSecondaireId = null;

    #[Assert\NotBlank(message: 'packSouhaiteId est requis')]
    #[Assert\Positive(message: 'packSouhaiteId doit être positif')]
    public int $packSouhaiteId;

    #[Assert\NotBlank(message: 'modeReglement est requis')]
    #[Assert\Choice(callback: [PaymentMethodEnum::class, 'values'], message: 'modeReglement invalide')]
    public string $modeReglement;

    public ?string $correctionToken = null;
}
