<?php

declare(strict_types=1);

namespace App\Service\SendingEmail;

use App\Entity\User;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;

readonly class SendExistingAccountEmailService
{
    public function __construct(
        private NotificationManager $notificationManager,
        private string $baseUrl,
    ) {
    }

    public function send(User $user): void
    {
        $notification = new EmailNotification(
            subject: 'Vous avez déjà un compte',
            content: 'Vous avez déjà un compte',
            template: 'emails/existingAccountNotice.html.twig',
            parameters: [
                'user' => $user,
                'loginUrl' => $this->baseUrl.'/login',
            ]
        );

        $this->notificationManager->send($notification, $user);
    }
}
