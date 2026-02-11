<?php

namespace App\Message;

final readonly class SendUpdateCoursEmailMessage
{
    public function __construct(
        private int $userCoursId,
        private \DateTimeInterface $initialDate,
        private int $initialDuration,
    ) {
    }

    public function getUserCoursId(): int
    {
        return $this->userCoursId;
    }

    public function getInitialDate(): \DateTimeInterface
    {
        return $this->initialDate;
    }

    public function getInitialDuration(): int
    {
        return $this->initialDuration;
    }
}
