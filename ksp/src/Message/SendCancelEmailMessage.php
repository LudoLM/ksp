<?php

namespace App\Message;

final readonly class SendCancelEmailMessage
{
    public function __construct(
        private int $userCoursId,
        private int $currentUserId
    ) {
    }

    public function getUserCoursId(): int
    {
        return $this->userCoursId;
    }

    public function getCurrentUserId(): int
    {
        return $this->currentUserId;
    }
}
