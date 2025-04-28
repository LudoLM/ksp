<?php

namespace App\Service\CoursControllerService\AddUserService;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class UserCreditCheckerService
{
    public function checkAccountCredits(User $user, bool $isAdmin): void
    {
        if ($user->getNombreCours() <= 0) {
            $message = $isAdmin
                ? "{$user->getPrenom()} n'a pas assez de crédits pour s'inscrire à ce cours."
                : "Vous n'avez pas assez de crédits pour vous inscrire à ce cours.";
            throw new \InvalidArgumentException($message, Response::HTTP_FORBIDDEN);
        }
    }
}
