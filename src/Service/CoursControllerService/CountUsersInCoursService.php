<?php

namespace App\Service\CoursControllerService;

use App\Entity\Cours;

class CountUsersInCoursService
{
    public function countUsers(Cours $cours): int
    {
        return count(array_filter(
            $cours->getUsersCours()->toArray(),
            fn (\App\Entity\UsersCours $usersCours): bool => true !== $usersCours->isOnWaitingList()
        ));
    }
}
