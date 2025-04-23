<?php

namespace App\Enum;

enum StatusCoursEnum: string
{
    case OUVERT = 'Ouvert';
    case COMPLET = 'Complet';
    case ANNULE = 'Annulé';
    case EN_CREATION = 'En création';
    case EN_COURS = 'En cours';
    case PASSE = 'Passé';
    case ARCHIVE = 'Archivé';

    public static function getStatusId(string $value): int
    {
        return match ($value) {
            self::OUVERT->value => 1,
            self::COMPLET->value => 2,
            self::ANNULE->value => 3,
            self::EN_CREATION->value => 4,
            self::EN_COURS->value => 5,
            self::PASSE->value => 6,
            self::ARCHIVE->value => 7,
            default => 0,
        };
    }
}
