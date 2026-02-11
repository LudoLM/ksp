<?php

namespace App\MessageHandler;

use App\Message\SendCancelEmailMessage;
use App\Repository\UsersCoursRepository;
use App\Service\SendingEmail\SendCancelEmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendCancelEmailMessageHandler
{
    public function __construct(
        private SendCancelEmailService $sendCancelEmailService,
        private UsersCoursRepository $usersCoursRepository,
    ) {
    }

    public function __invoke(SendCancelEmailMessage $message): void
    {
        $userCours = $this->usersCoursRepository->find($message->getUserCoursId());

        if (null === $userCours) {
            throw new \Exception('UserCours not found');
        }

        $this->sendCancelEmailService->send($userCours);
    }
}
