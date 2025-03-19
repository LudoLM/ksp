<?php

namespace App\Message;

class UpdateStatusCoursMessage
{
    public function __construct(
        private readonly int $coursId,
    ) {
    }

    public function getCoursId(): int
    {
        return $this->coursId;
    }
}
