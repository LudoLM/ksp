<?php

declare(strict_types=1);

namespace App\Enum;

enum PaymentMethodEnum: string
{
    case CARD_ONE_TIME = 'CbComptant';
    case CHECK_2X = 'Cheque2x';
    case CHECK_3X = 'Cheque3x';
    case CHECK_10X = 'Cheque10x';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
