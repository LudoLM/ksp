<?php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\StatusCours;
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
        private readonly EntityManagerInterface $entityManager
    )
    {
    }
    public function update(Cours $cours)
    {

        switch ($cours->getStatusCours()->getLibelle()) {
            case StatusCoursEnum::OUVERT->value:
            case StatusCoursEnum::COMPLET->value:
                $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_COURS->value]));
                $this->entityManager->persist($cours);
                $this->entityManager->flush();
                $delay = ($cours->getDateCours()->getTimestamp() + $cours->getDuree()) - time();
                $this->messageBus->dispatch(
                    new UpdateStatusCoursMessage(
                        $cours->getId()),
                    [new DelayStamp($delay)]
                );
                break;
            case StatusCoursEnum::EN_COURS->value:
                $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::PASSE->value]));
                $this->entityManager->persist($cours);
                $this->entityManager->flush();
                $delay = ($cours->getDateCours()->getTimestamp() + (60 * 60 * 24 * 30)) - time();
                $this->messageBus->dispatch(
                    new UpdateStatusCoursMessage(
                        $cours->getId()),
                    [new DelayStamp($delay)]
                );
                break;
            case StatusCoursEnum::PASSE->value:
                $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ARCHIVE->value]));
                $this->entityManager->persist($cours);
                $this->entityManager->flush();
                break;
        }
    }
}
