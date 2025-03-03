<?php

namespace App\Service\CoursControllerService;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AddUserTimeCheckerService
{

    private const INSCRIPTION_LIMIT = 1800;
    public function isTooLateRegister($cours) : bool
    {

        return $cours->getDateCours()->getTimestamp() - time() < self::INSCRIPTION_LIMIT;
    }

}
