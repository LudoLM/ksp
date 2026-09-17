<?php

namespace App\MessageHandler;

use App\Message\SendExistingAccountEmailMessage;
use App\Repository\UserRepository;
use App\Service\SendingEmail\SendExistingAccountEmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendExistingAccountEmailMessageHandler
{
    public function __construct(
        private SendExistingAccountEmailService $sendExistingAccountEmailService,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(SendExistingAccountEmailMessage $message): void
    {
        $user = $this->userRepository->find($message->getUserId());

        if (null === $user) {
            throw new \Exception('Utilisateur non trouvé');
        }

        $this->sendExistingAccountEmailService->send($user);
    }
}
