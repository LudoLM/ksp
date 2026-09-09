<?php

declare(strict_types=1);

namespace App\Exception;

final class UnvalidatedWishesFormException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Votre dossier d'inscription doit être validé avant de créer votre compte. Merci de contacter l'administration.");
    }
}
