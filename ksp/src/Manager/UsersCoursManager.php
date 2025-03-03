<?php

namespace App\Manager;

use App\Entity\Cours;
use App\Entity\User;
use App\Entity\UsersCours;

class UsersCoursManager
{

    public function addUserToCours(Cours $cours, bool $isEnAttente, User $user) : Cours
    {
        $usersCours = new UsersCours();
        $usersCours->setUser($user);
        $usersCours->setCreatedAt(new \DateTimeImmutable());
        $usersCours->setEnAttente($isEnAttente);
        $cours->addUsersCours($usersCours);

        return $cours;
    }
}
