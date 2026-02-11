<?php

namespace App\Message;

readonly class SendDesistementEmailMessage
{
    public function __construct(
        private int $coursId,
        private int $userId,
    ) {
    }

    public function getCoursId(): int
    {
        return $this->coursId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
