<?php

namespace App\MessageHandler;

use App\Message\SendPasswordChangedNotificationMessage;
use App\Repository\UserRepository;
use App\Service\SendingEmail\SendPasswordChangedNotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendPasswordChangedNotificationMessageHandler
{
    public function __construct(
        private SendPasswordChangedNotificationService $sendPasswordChangedNotificationService,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(SendPasswordChangedNotificationMessage $message): void
    {
        $user = $this->userRepository->find($message->getUserId());
        if (null !== $user) {
            $this->sendPasswordChangedNotificationService->send($user);
        }
    }
}
