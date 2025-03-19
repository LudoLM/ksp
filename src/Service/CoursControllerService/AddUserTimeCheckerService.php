<?php

namespace App\Service\CoursControllerService;

use App\Entity\Cours;

class AddUserTimeCheckerService
{
    private const INSCRIPTION_LIMIT = 1800;

    public function isTooLateRegister(Cours $cours): bool
    {
        return $cours->getDateCours()->getTimestamp() - time() < self::INSCRIPTION_LIMIT;
    }
}
