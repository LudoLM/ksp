<?php

namespace App\Service\CoursControllerService\AddUserService;

use App\Entity\Cours;
use App\Entity\User;
use App\Entity\UsersCours;
use App\Manager\UsersCoursManager;
use App\Service\NotificationService\NotificationsUsersActionsService;

readonly class CoursParticipationService
{
    public function __construct(
        private UsersCoursManager $usersCoursManager,
        private NotificationsUsersActionsService $notificationsUsersActionsService,
    ) {
    }

    public function addUserToCoursOrWaitingList(Cours $cours, User $user, bool $isOnWaitingList): void
    {
        // Si le cours n'est pas complet, je vérifie si l'utilisateur est déjà inscrit ou en attente
        $usersCoursFiltered = array_filter($cours->getUsersCours()->toArray(), fn (UsersCours $usersCours): bool => $usersCours->getUser() === $user);

        // Si l'utilisateur est déjà en attente, je le passe en inscrit
        if (count($usersCoursFiltered) > 0) {
            $usersCours = array_values($usersCoursFiltered)[0];
            $usersCours->setIsOnWaitingList(false);
            $usersCours->setCreatedAt(new \DateTimeImmutable());
            $usersCours->setUnsubscribedAt(null);
            $this->notificationsUsersActionsService->sendNotifications($usersCours);
        } //      Si l'utilisateur n'est pas inscrit, je l'ajoute à la liste des participants
        else {
            $this->usersCoursManager->addUserToCours($cours, $isOnWaitingList, $user);
        }
    }
}
