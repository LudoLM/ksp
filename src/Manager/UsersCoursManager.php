<?php

namespace App\Manager;

use App\Entity\Cours;
use App\Entity\User;
use App\Entity\UsersCours;
use App\Service\CoursControllerService\CountUsersInCoursService;
use App\Service\NotificationService\NotificationsUsersActionsService;

class UsersCoursManager
{
    public function __construct(
        private readonly CountUsersInCoursService $countUsersInCoursService,
        private readonly NotificationsUsersActionsService $notificationsUsersActionsService,
    ) {
    }

    public function addUserToCours(Cours $cours, bool $isOnWaitingList, User $user): Cours
    {
        $usersCours = new UsersCours();
        $usersCours->setUser($user);
        $usersCours->setCreatedAt(new \DateTimeImmutable());
        $usersCours->setUnsubscribedAt(null);
        $usersCours->setIsOnWaitingList($isOnWaitingList);
        $cours->addUsersCours($usersCours);
        $this->notificationsUsersActionsService->sendNotifications($usersCours);

        return $cours;
    }

    public function processRemovalFromCours(Cours $cours, array $participantIds, bool $isOnWaitingList = false): void
    {
        foreach ($cours->getUsersCours() as $usersCours) {
            if (in_array($usersCours->getUser()->getId(), $participantIds, true)) {
                $usersCours->setUnsubscribedAt(new \DateTimeImmutable());
                if (!$isOnWaitingList) {
                    $usersCours->getUser()->setNombreCours($usersCours->getUser()->getNombreCours() + 1);
                }
                $this->notificationsUsersActionsService->sendNotifications($usersCours);
            }
        }
        $this->countUsersInCoursService->reopenIfCapacityAvailable($cours);
    }
}
