<?php

namespace App\Service\SendingEmail;

use App\Entity\Cours;
use App\Entity\User;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;

readonly class SendDesistementEmailService
{
    public function __construct(
        private NotificationManager $notificationManager,
        private string $baseUrl,
    ) {
    }

    public function send(User $user, Cours $cours): void
    {
        $notification = new EmailNotification(
            subject: 'Place disponible pour le cours',
            content: 'Place disponible pour le cours',
            template: 'emails/attente.html.twig',
            parameters: [
                'cours' => $cours,
                'participant' => $user,
                'url' => $this->baseUrl.'/coursDetails/'.$cours->getId(),
            ]
        );

        $this->notificationManager->send($notification, $user);
    }
}
