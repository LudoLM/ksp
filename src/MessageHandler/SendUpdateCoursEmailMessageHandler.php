<?php

namespace App\MessageHandler;

use App\Message\SendUpdateCoursEmailMessage;
use App\Repository\UsersCoursRepository;
use App\Service\SendingEmail\SendUpdateCoursEmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendUpdateCoursEmailMessageHandler
{
    public function __construct(
        private SendUpdateCoursEmailService $sendUpdateCoursEmailEmailService,
        private UsersCoursRepository $usersCoursRepository,
    ) {
    }

    public function __invoke(SendUpdateCoursEmailMessage $message): void
    {
        $userCours = $this->usersCoursRepository->find($message->getUserCoursId());

        if (null === $userCours) {
            throw new \Exception('UserCours not found');
        }

        $this->sendUpdateCoursEmailEmailService->send($userCours, $message->getInitialDate(), $message->getInitialDuration());
    }
}
