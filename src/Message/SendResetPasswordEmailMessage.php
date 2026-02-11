<?php

namespace App\Message;

readonly class SendResetPasswordEmailMessage
{
    public function __construct(
        private int $userId,
        private string $token,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
