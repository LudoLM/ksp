<?php

namespace App\DTO;

use App\Service\Interface\Notification\RecipientInterface;

readonly class UserContactDTO implements RecipientInterface
{
    public function __construct(
        private int $id,
        private string $prenom,
        private string $nom,
        private string $email,
        private string $telephone,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
}
