<?php

namespace App\Service;

use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Message\UpdateStatusCoursMessage;
use App\Repository\StatusCoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class UpdateStatusCoursService
{
    public function __construct(
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function update(Cours $cours): void
    {
        switch ($cours->getStatusCours()->getLibelle()) {
            // Passer le cours à l'état "En cours" et planifier un message pour le passer à l'état "Passé" à la fin du cours
            case StatusCoursEnum::OUVERT->value:
            case StatusCoursEnum::COMPLET->value:
                $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_COURS->value]));
                $this->entityManager->persist($cours);
                $this->entityManager->flush();
                // Fixer le délai à la durée du cours
                $delay = $cours->getDuree() * 60 * 1000;
                $this->messageBus->dispatch(
                    new UpdateStatusCoursMessage(
                        $cours->getId()),
                    [new DelayStamp($delay)]
                );
                break;

                // Passer le cours à l'état "Passé" et planifier un message pour le passer à l'état "Archive" à la fin du cours
            case StatusCoursEnum::EN_COURS->value:
                $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::PASSE->value]));
                $this->entityManager->persist($cours);
                $this->entityManager->flush();
                // Fixer le délai à 30 jours
                $delay = 60 * 60 * 24 * 30 * 1000;
                $this->messageBus->dispatch(
                    new UpdateStatusCoursMessage(
                        $cours->getId()),
                    [new DelayStamp($delay)]
                );
                break;

                // Passer le cours à l'état "Archive"
            case StatusCoursEnum::PASSE->value:
                $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ARCHIVE->value]));
                $this->entityManager->persist($cours);
                $this->entityManager->flush();
                break;
        }
    }
}
