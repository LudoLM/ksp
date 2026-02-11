<?php

namespace App\Message;

final readonly class SendCancelEmailMessage
{
    public function __construct(
        private int $userCoursId,
    ) {
    }

    public function getUserCoursId(): int
    {
        return $this->userCoursId;
    }
}
