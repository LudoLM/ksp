<?php

namespace App\Enum;

enum StatusCoursEnum: string
{
    case OUVERT = "Ouvert";
    case FERME = "Fermé";
    case ANNULE = "Annulé";
    case EN_CREATION = "En création";
    case EN_COURS = "En cours";
    case PASSE = "Passé";
    case ARCHIVE = "Archivé";

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCssClass(): string
    {
        return match ($this) {
            self::OUVERT => "bg-success",
            self::FERME => 'bg-danger',
            self::ANNULE => 'bg-warning',
            self::EN_CREATION => 'bg-info',
            self::EN_COURS => 'bg-primary',
            self::PASSE => 'bg-secondary',
            self::ARCHIVE => 'bg-secondary',
        };
    }

    public static function getCssClassByLibelle(string $libelle): string
    {
        foreach (self::cases() as $case) {
            if ($case->getValue() === $libelle) {
                return $case->getCssClass();
            }
        }

        throw new \InvalidArgumentException("Invalid status label: $libelle");
    }
}
