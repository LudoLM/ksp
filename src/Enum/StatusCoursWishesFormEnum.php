<?php

declare(strict_types=1);

namespace App\Enum;

enum StatusCoursWishesFormEnum: string
{
    case EN_ATTENTE = 'EnAttente';
    case VALIDE = 'Valide';
    case A_CORRIGER = 'ACorriger';
}
