<?php

namespace App\Manager;

use App\Entity\Cours;
use App\Entity\User;
use App\Entity\UsersCours;

class UsersCoursManager
{

    public function addUserToCours(Cours $cours, bool $isOnWaitingList, User $user) : Cours
    {
        $usersCours = new UsersCours();
        $usersCours->setUser($user);
        $usersCours->setCreatedAt(new \DateTimeImmutable());
        $usersCours->setIsOnWaitingList($isOnWaitingList);
        $cours->addUsersCours($usersCours);

        return $cours;
    }
}
