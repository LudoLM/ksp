<?php

namespace App\MessageHandler;

use App\Message\RemoveResetTokenMessage;
use App\Service\RemoveResetTokenService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class RemoveResetTokenMessageHandler
{
    public function __construct(
        private RemoveResetTokenService $removeResetTokenService,
    ) {
    }

    public function __invoke(RemoveResetTokenMessage $message): void
    {
        $this->removeResetTokenService->removeResetToken($message->getUserId());
    }
}
