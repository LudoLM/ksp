<?php

namespace App\Service\SendingEmail;

use App\Entity\User;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;

readonly class SendPasswordChangedNotificationService
{
    public function __construct(
        private NotificationManager $notificationManager,
    ) {
    }

    public function send(User $user): void
    {
        $notification = new EmailNotification(
            subject: 'Votre mot de passe a été modifié',
            content: 'Votre mot de passe a été modifié',
            template: 'emails/passwordChanged.html.twig',
            parameters: [
                'user' => $user,
                'changedAt' => new \DateTimeImmutable(),
            ]);
        $this->notificationManager->send($notification, $user);
    }
}
