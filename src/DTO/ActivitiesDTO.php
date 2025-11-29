<?php

namespace App\DTO;

use App\Entity\HistoriquePaiement;
use App\Entity\UsersCours;

class ActivitiesDTO
{
    public function __construct(
        public readonly string $type,
        public readonly \DateTimeInterface $dateAction,
        public readonly string $userName,
        public readonly string $subject,
        public readonly \DateTimeInterface $dateSubject,
    ) {
    }

    public static function fromUsersCours(UsersCours $uc): ActivitiesDTO
    {
        $isUnsubscribe = $uc->getUnsubscribedAt() instanceof \DateTimeImmutable;
        $isOnWaitingList = $uc->isOnWaitingList();

        $type = match (true) {
            $isUnsubscribe && $isOnWaitingList => 'Supp attente',
            $isUnsubscribe && !$isOnWaitingList => 'Désinscription',
            !$isUnsubscribe && $isOnWaitingList => 'En attente',
            default => 'Inscription',
        };

        return new self(
            type: $type,
            dateAction: $uc->getUnsubscribedAt() ?? $uc->getCreatedAt(),
            userName: $uc->getUser()->getPrenom().' '.$uc->getUser()->getNom(),
            subject: $uc->getCours()->getTypeCours()->getLibelle(),
            dateSubject: $uc->getCours()->getDateCours(),
        );
    }

    public static function fromHistoriquePaiement(HistoriquePaiement $hp): ActivitiesDTO
    {
        return new self(
            type: 'Achat de cours',
            dateAction: $hp->getDate(),
            userName: $hp->getUser()->getPrenom().' '.$hp->getUser()->getNom(),
            subject: $hp->getPack()->getNom(),
            dateSubject: $hp->getDate(),
        );
    }
}
