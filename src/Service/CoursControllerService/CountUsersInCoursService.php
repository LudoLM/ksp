<?php

namespace App\Service\CoursControllerService;

use App\Entity\Cours;
use App\Entity\UsersCours;

class CountUsersInCoursService
{
    public function countUsers(Cours $cours): int
    {
        return count(array_filter(
            $cours->getUsersCours()->toArray(),
            fn (UsersCours $usersCours): bool => true !== $usersCours->isOnWaitingList()
        ));
    }
}
