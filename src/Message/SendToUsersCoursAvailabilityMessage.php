<?php

namespace App\Message;

final readonly class SendToUsersCoursAvailabilityMessage
{
    public function __construct(
        private int $userId,
        private array $coursAvailabilities,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCoursAvailabilities(): array
    {
        return $this->coursAvailabilities;
    }
}
