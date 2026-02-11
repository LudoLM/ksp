<?php

namespace App\Service\SendingEmail;

use App\Entity\UsersCours;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;

readonly class SendUpdateCoursEmailService
{
    public function __construct(
        private NotificationManager $notificationManager,
        private string $baseUrl,
    ) {
    }

    public function send(UsersCours $usersCours, \DateTimeInterface $initialDate, int $initialDuration): void
    {
        $notification = new EmailNotification(
            subject: 'Modification de cours',
            content: 'Cours Modifié',
            template: 'emails/updateCours.html.twig',
            parameters: [
                'cours' => $usersCours->getCours(),
                'participant' => $usersCours->getUser(),
                'url' => $this->baseUrl,
                'initialDate' => $initialDate,
                'initialDuration' => $initialDuration,
            ]
        );

        $this->notificationManager->send($notification, $usersCours->getUser());
    }
}
