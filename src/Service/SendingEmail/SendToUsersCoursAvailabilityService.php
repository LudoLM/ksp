<?php

namespace App\Service\SendingEmail;

use App\DTO\UserContactDTO;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;

readonly class SendToUsersCoursAvailabilityService
{
    public function __construct(
        private NotificationManager $notificationManager,
        private string $baseUrl,
    ) {
    }

    public function send(UserContactDTO $user, array $coursAvailabilities): void
    {
        $notification = new EmailNotification(
            subject: 'Disponibilités de la semaine',
            content: 'Disponibilités de la semaine',
            template: 'emails/coursAvailability.html.twig',
            parameters: [
                'user' => $user,
                'coursAvailabilities' => $coursAvailabilities,
                'url' => $this->baseUrl,
            ]
        );

        $this->notificationManager->send($notification, $user);
    }
}
