<?php

namespace App\Message;

final readonly class SendCertificateStatusEmailMessage
{
    public function __construct(
        private int $certificateId,
    ) {
    }

    public function getCertificateId(): int
    {
        return $this->certificateId;
    }
}
