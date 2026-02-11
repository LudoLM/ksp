<?php

namespace App\Service\Interface\Notification;

interface NotificationInterface
{
    public function getSubject(): string;

    public function getContent(): string;

    public function getType(): string; // 'email', 'sms', 'push'

    public function getTemplate(): ?string;

    public function getParameters(): array;
}
