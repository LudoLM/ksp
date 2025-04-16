<?php

namespace App\MessageHandler;

use App\Message\SendUpdateCoursEmailMessage;
use App\Repository\UserRepository;
use App\Repository\UsersCoursRepository;
use App\Service\SendingEmail\SendUpdateCoursEmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendUpdateCoursEmailMessageHandler
{
    public function __construct(
        private SendUpdateCoursEmailService $sendUpdateCoursEmailEmailService,
        private UsersCoursRepository $usersCoursRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(SendUpdateCoursEmailMessage $message): void
    {
        $userCours = $this->usersCoursRepository->find($message->getUserCoursId());
        $currentUser = $this->userRepository->find($message->getCurrentUserId());

        if (null === $userCours) {
            throw new \Exception('UserCours not found');
        }
        if (null === $currentUser) {
            throw new \Exception('CurrentUser not found');
        }

        $this->sendUpdateCoursEmailEmailService->send($userCours, $currentUser, $message->getInitialDate(), $message->getInitialDuration());
    }
}
