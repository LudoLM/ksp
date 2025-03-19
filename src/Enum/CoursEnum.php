<?php

namespace App\Enum;

enum CoursEnum
{
    case PILATES_DEBUTANT;
    case STRETCHING;
    case GYM_ACTIV_BALL;
    case CIRCUIT_TRAINING;
    case PILATES_INTERMEDIAIRES;
    case GYM_DOUCE;

    public function getValue(): string
    {
        return match ($this) {
            self::PILATES_DEBUTANT => 'Pilates débutant',
            self::STRETCHING => 'Stretching',
            self::GYM_ACTIV_BALL => 'Gym Activ Ball',
            self::CIRCUIT_TRAINING => 'Circuit Training',
            self::PILATES_INTERMEDIAIRES => 'Pilates Intermédiaires',
            self::GYM_DOUCE => 'Gym douce',
        };
    }
}
