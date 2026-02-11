<?php

namespace App\MessageHandler;

use App\Message\SendResetPasswordEmailMessage;
use App\Repository\UserRepository;
use App\Service\SendingEmail\SendResetPasswordEmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendResetPasswordEmailMessageHandler
{
    public function __construct(
        private UserRepository $usersRepository,
        private SendResetPasswordEmailService $sendResetPasswordEmailService,
    ) {
    }

    public function __invoke(SendResetPasswordEmailMessage $message): void
    {
        $user = $this->usersRepository->find($message->getUserId());

        if (null === $user) {
            throw new \Exception('User not found');
        }

        $this->sendResetPasswordEmailService->send($user, $message->getToken());
    }
}
