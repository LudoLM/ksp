<?php
namespace App\MessageHandler;

use App\Message\SendCancelEmailMessage;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use App\Repository\UsersCoursRepository;
use App\Service\SendCancelEmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendCancelEmailMessageHandler
{
    public function __construct(
        private SendCancelEmailService $sendCancelEmailService,
        private UsersCoursRepository   $usersCoursRepository,
        private UserRepository          $userRepository

    ) {
    }

    public function __invoke(SendCancelEmailMessage $message): void
    {
        $userCours = $this->usersCoursRepository->find($message->getUserCoursId());
        $currentUser = $this->userRepository->find($message->getCurrentUserId());

        if ($userCours === null) {
            throw new \Exception('UserCours not found');
        }
        if ($currentUser === null) {
            throw new \Exception('CurrentUser not found');
        }

        $this->sendCancelEmailService->send($userCours, $currentUser);
    }
}
