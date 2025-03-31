<?php

namespace App\Message;

readonly class RemoveResetTokenMessage
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
