<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * La saison commence en septembre : du 1er septembre AAAA au 31 août AAAA+1
 * est la saison "AAAA-AAAA+1".
 */
final class SaisonHelper
{
    private const int SEASON_START_MONTH = 9;

    private function __construct()
    {
    }

    public static function current(?\DateTimeImmutable $now = null): string
    {
        $now ??= new \DateTimeImmutable();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('n');

        if ($month >= self::SEASON_START_MONTH) {
            return "{$year}-".($year + 1);
        }

        return ($year - 1)."-{$year}";
    }
}
