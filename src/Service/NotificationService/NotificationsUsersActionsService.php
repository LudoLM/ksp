<?php

namespace App\Service\NotificationService;

use App\DTO\ActivitiesDTO;
use App\Entity\UsersCours;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

readonly class NotificationsUsersActionsService
{
    public function __construct(
        private HubInterface $hub,
        private SerializerInterface $serializer,
    ) {
    }

    public function sendNotifications(UsersCours $userCours): void
    {
        $update = new Update(
            topics: 'admin/notifications',
            data: $this->serializer->serialize([
                'type' => 'Notifications',
                'content' => ActivitiesDTO::fromUsersCours($userCours),
                'timestamp' => new \DateTimeImmutable()->format('c'),
            ], 'json')
        );

        $this->hub->publish($update);
    }

    public function toNotification(array $usersCours, array $historique_paiements): array
    {
        $activities = [];

        foreach ($usersCours as $userCours) {
            $activities[] = ActivitiesDTO::fromUsersCours($userCours);
        }

        foreach ($historique_paiements as $paiement) {
            $activities[] = ActivitiesDTO::fromHistoriquePaiement($paiement);
        }

        // 1. Tri du tableau fusionné par date décroissante
        usort($activities, function (ActivitiesDTO $a, ActivitiesDTO $b): int {
            $timeA = $a->dateAction;
            $timeB = $b->dateAction;

            return $timeB <=> $timeA;
        });

        return $activities;
    }
}
