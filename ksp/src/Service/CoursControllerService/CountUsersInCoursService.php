<?php

namespace App\Service\CoursControllerService;

class CountUsersInCoursService
{
    public function countUsers($cours): int
    {
        return count(array_filter(
            $cours->getUsersCours()->toArray(),
            function ($usersCours) {
                return !$usersCours->isOnWaitingList();
            }
        ));
    }
}
