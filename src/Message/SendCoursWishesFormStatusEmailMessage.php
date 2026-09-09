<?php

namespace App\Message;

final readonly class SendCoursWishesFormStatusEmailMessage
{
    public function __construct(
        private int $formId,
        private ?string $registrationToken = null,
    ) {
    }

    public function getFormId(): int
    {
        return $this->formId;
    }

    public function getRegistrationToken(): ?string
    {
        return $this->registrationToken;
    }
}
