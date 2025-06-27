<?php

namespace App\Helper;

class DateHelper
{
    public static function adjustDatesForCalendarRoute(\DateTime $dateCours): array
    {
        $dateStart = clone $dateCours;
        $dateStart->modify('monday this week');
        $dateLimit = clone $dateStart;
        $dateLimit->modify('+6 days');

        return [$dateStart, $dateLimit];
    }

    public static function adjustDatesForIndexRoute(\DateTime $dateCours): array
    {
        $dateLimit = clone $dateCours;
        $dateLimit->modify('+1 month');

        return [$dateCours, $dateLimit];
    }
}
