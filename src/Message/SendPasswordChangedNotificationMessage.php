<?php

namespace App\Message;

final readonly class SendPasswordChangedNotificationMessage
{
    public function __construct(
        private int $userId,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
