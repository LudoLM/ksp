<?php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;

readonly class UserAnonymisationService
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Anonymise les données personnelles d'un utilisateur.
     * Évite la ré-anonymisation si déjà anonymisé.
     *
     * @param User   $user   Utilisateur à anonymiser
     * @param string $reason Motif de l'anonymisation : 'rgpd_request' (suppression immédiate) ou 'inactivity' (archivage 1+ an)
     *
     * @return bool true si anonymisé, false si déjà anonymisé
     */
    public function anonymiseUser(User $user, string $reason = 'rgpd_request'): bool
    {
        $userId = $user->getId();

        // Éviter la ré-anonymisation
        if ($user->getAnonymisedAt() instanceof \DateTimeInterface) {
            $this->logger->warning("Utilisateur {$userId} déjà anonymisé, opération ignorée", [
                'user_id' => $userId,
                'action' => 'anonymiser',
                'reason' => $reason,
                'already_anonymised_at' => $user->getAnonymisedAt()->format('Y-m-d H:i:s'),
            ]);

            return false;
        }

        // Anonymiser les données personnelles
        $user->setEmail("anonyme-{$userId}@deleted.local");
        $user->setPrenom('Anonyme');
        $user->setNom('Anonyme');
        $user->setTelephone(null);
        $user->setAdresse(null);
        $user->setCodePostal(null);
        $user->setCommune(null);
        $user->setAnonymisedAt(new \DateTime());

        $this->logger->info("Utilisateur {$userId} anonymisé", [
            'user_id' => $userId,
            'action' => 'anonymiser',
            'reason' => $reason,
            'is_deleted' => $user->isDeleted(),
            'is_archived' => $user->isArchived(),
        ]);

        return true;
    }
}
