<?php

namespace App\Helper;

class DateHelper
{
    public static function adjustDatesForCalendarRoute(\DateTime $dateCours): array
    {
        $dateCours->modify('monday this week');
        $dateLimit = clone $dateCours;
        $dateLimit->modify('+6 days');

        return [$dateCours, $dateLimit];
    }

    public static function adjustDatesForIndexRoute(\DateTime $dateCours): array
    {
        $dateLimit = clone $dateCours;
        $dateLimit->modify('+1 month');

        return [$dateCours, $dateLimit];
    }
}
