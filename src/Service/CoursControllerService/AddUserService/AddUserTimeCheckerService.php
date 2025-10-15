<?php

namespace App\Service\CoursControllerService\AddUserService;

use App\Entity\Cours;

class AddUserTimeCheckerService
{
    private const int INSCRIPTION_LIMIT = 1800;

    public function isTooLateRegister(Cours $cours): void
    {
        if ($cours->getDateCours()->getTimestamp() - time() < self::INSCRIPTION_LIMIT) {
            throw new \InvalidArgumentException('Inscription impossible : les inscriptions doivent être effectuées au moins 30 minutes avant le début du cours.', 403);
        }
    }
}
