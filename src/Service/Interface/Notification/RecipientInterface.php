<?php

namespace App\Service\Interface\Notification;

interface RecipientInterface
{
    public function getEmail(): ?string;

    public function getTelephone(): ?string;
}
