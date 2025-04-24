<?php

namespace App\Helper;

use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Message\UpdateStatusCoursMessage;
use App\Repository\StatusCoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class UpdateStatusCoursHelper
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function checkCoursUpdatable(Cours $cours, StatusCoursEnum $statusCours, \DateTimeImmutable $dateToCompare, int $delay): void
    {
        $dateNow = new \DateTimeImmutable();

        if ($dateToCompare->format('Y-m-d H:i') === $dateNow->format('Y-m-d H:i')) {
            $this->setNewStatus($cours, $statusCours);
            if ($delay > 0) {
                $this->messageBus->dispatch(
                    new UpdateStatusCoursMessage(
                        $cours->getId()),
                    [new DelayStamp($delay)]
                );
            }
        }
    }

    public function setNewStatus(Cours $cours, StatusCoursEnum $statusCours): void
    {
        $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => $statusCours->value]));
        $this->entityManager->persist($cours);
        $this->entityManager->flush();
    }
}
