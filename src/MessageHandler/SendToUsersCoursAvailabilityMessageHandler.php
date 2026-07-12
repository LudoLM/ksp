<?php

namespace App\MessageHandler;

use App\DTO\UserContactDTO;
use App\Message\SendToUsersCoursAvailabilityMessage;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use App\Service\SendingEmail\SendToUsersCoursAvailabilityService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class SendToUsersCoursAvailabilityMessageHandler
{
    public function __construct(
        private UserRepository $usersRepository,
        private CoursRepository $coursRepository,
        private LoggerInterface $logger,
        private SendToUsersCoursAvailabilityService $sendToUsersCoursAvailabilityService,
    ) {
    }

    public function __invoke(SendToUsersCoursAvailabilityMessage $message): void
    {
        $userId = $message->getUserId();

        $user = $this->usersRepository->findUserContact($userId);
        if (!$user instanceof UserContactDTO) {
            throw new \Exception('Utilisateur non trouvé'.$userId);
        }
        $coursAvailabilities = $this->coursRepository->findCoursAvailabilitiesByIds($message->getCoursAvailabilities());
        if ([] === $coursAvailabilities) {
            $this->logger->info(sprintf('Aucun cours disponible pour l\'utilisateur %d, envoi annulé', $userId));

            return;
        }

        $this->sendToUsersCoursAvailabilityService->send($user, $coursAvailabilities);
    }
}
