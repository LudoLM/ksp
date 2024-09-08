<?php

namespace App\Enum;

enum StatusCoursEnum: string
{
    case OUVERT = "Ouvert";
    case COMPLET = "Complet";
    case ANNULE = "Annulé";
    case EN_CREATION = "En création";
    case EN_COURS = "En cours";
    case PASSE = "Passé";
    case ARCHIVE = "Archivé";

    public function getValue(): string
    {
        return $this->value;
    }
}
