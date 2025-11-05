<?php

namespace App\Service\CoursControllerService;

use App\Entity\Cours;
use App\Entity\UsersCours;
use App\Enum\StatusCoursEnum;
use App\Repository\StatusCoursRepository;

class CountUsersInCoursService
{
    public function __construct(
        private readonly StatusCoursRepository $statusCoursRepository,
    ) {
    }

    public function countUsers(Cours $cours): int
    {
        return count(array_filter(
            $cours->getUsersCours()->toArray(),
            fn (UsersCours $usersCours): bool => true !== $usersCours->isOnWaitingList()
        ));
    }

    public function reopenIfCapacityAvailable(Cours $cours): void
    {
        if ($cours->hasCapacityAvailable() && $cours->getStatusCours()->getLibelle() === StatusCoursEnum::COMPLET->value) {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));
            // Envoi d'un mail aux personnes en attente
            /*$eventCours = new DesistementEvent($cours);
            $this->dispatcher->dispatch($eventCours);*/
        }
    }
}
