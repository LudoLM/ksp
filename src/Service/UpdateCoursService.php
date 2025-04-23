<?php

namespace App\Service;

use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Service\CoursControllerService\UpdateStatusCoursService;

readonly class UpdateCoursService
{
    // Délai d'archivage en jours

    public function __construct(
        private UpdateStatusCoursService $updateStatusCoursService,
    ) {
    }

    public function update(Cours $cours): void
    {
        switch ($cours->getStatusCours()->getLibelle()) {
            // Passer le cours à l'état "En cours" et planifier un message pour le passer à l'état "Passé" à la fin du cours
            case StatusCoursEnum::OUVERT->value:
            case StatusCoursEnum::COMPLET->value:
                $this->updateStatusCoursService->updateToEnCours($cours);
                break;

                // Passer le cours à l'état "Passé" et planifier un message pour le passer à l'état "Archive" à la fin du cours
            case StatusCoursEnum::EN_COURS->value:
                $this->updateStatusCoursService->updateToPasse($cours);
                break;

                // Passer le cours à l'état "Archive"
            case StatusCoursEnum::PASSE->value:
                $this->updateStatusCoursService->updateToArchive($cours);
                break;
        }
    }
}
