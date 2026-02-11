<?php

namespace App\Service\Notification;

use App\Service\Interface\Notification\NotificationInterface;

readonly class EmailNotification implements NotificationInterface
{
    public function __construct(
        private string $subject,
        private string $content,
        private string $template,
        private array $parameters = [],
        private string $type = 'email',
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
