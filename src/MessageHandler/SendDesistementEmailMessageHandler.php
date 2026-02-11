<?php

namespace App\MessageHandler;

use App\Message\SendDesistementEmailMessage;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use App\Service\SendingEmail\SendDesistementEmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendDesistementEmailMessageHandler
{
    public function __construct(
        private SendDesistementEmailService $sendDesistementEmailService,
        private CoursRepository $coursRepository,
        private UserRepository $usersRepository,
    ) {
    }

    public function __invoke(SendDesistementEmailMessage $message): void
    {
        $user = $this->usersRepository->find($message->getUserId());
        $cours = $this->coursRepository->find($message->getCoursId());

        if (null === $user) {
            throw new \Exception('User not found');
        }

        if (null === $cours) {
            throw new \Exception('Cours not found');
        }

        $this->sendDesistementEmailService->send($user, $cours);
    }
}
