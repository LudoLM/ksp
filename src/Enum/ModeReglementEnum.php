<?php

declare(strict_types=1);

namespace App\Enum;

enum ModeReglementEnum: string
{
    case CB_COMPTANT = 'CbComptant';
    case CHEQUE_2X = 'Cheque2x';
    case CHEQUE_3X = 'Cheque3x';
    case CHEQUE_10X = 'Cheque10x';
}
