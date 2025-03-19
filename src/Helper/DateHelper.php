<?php

namespace App\Helper;

class DateHelper
{
    public static function adjustDatesForCalendarRoute(\DateTime $dateCours, int $currentPage): array
    {
        $dateCours->modify('+'.($currentPage - 1) * 7 .' days');
        $dateCours->modify('monday this week');
        $dateLimit = clone $dateCours;
        $dateLimit->modify('+6 days');

        return [$dateCours, $dateLimit];
    }
}
