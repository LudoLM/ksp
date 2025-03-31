<?php

namespace App\Message;

readonly class UpdateStatusCoursMessage
{
    public function __construct(
        private int $coursId,
    ) {
    }

    public function getCoursId(): int
    {
        return $this->coursId;
    }
}
