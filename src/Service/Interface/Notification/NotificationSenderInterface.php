<?php

namespace App\Service\Interface\Notification;

interface NotificationSenderInterface
{
    public function send(NotificationInterface $notification, RecipientInterface $recipient): void;

    public function supports(NotificationInterface $notification): bool;
}
