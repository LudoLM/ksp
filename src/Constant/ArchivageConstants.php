<?php

declare(strict_types=1);

namespace App\Constant;

final class ArchivageConstants
{
    /**
     * Nombre de mois d'inactivité avant archivage d'un utilisateur.
     * Un utilisateur est considéré comme inactif s'il n'a pas accédé à la plateforme
     * pendant cette durée ET a zéro cours restants.
     */
    public const int MONTHS_INACTIVE_THRESHOLD = 12;

    /**
     * Nombre de mois d'attente après archivage avant anonymisation d'un utilisateur.
     * Les utilisateurs archivés depuis cette durée sont anonymisés pour respecter la RGPD.
     */
    public const int ANONYMISATION_DELAY_MONTHS = 12;

    private function __construct()
    {
        // Cette classe ne peut pas être instanciée
    }
}
