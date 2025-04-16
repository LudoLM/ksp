<?php

namespace App\Service;

use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Message\UpdateStatusCoursMessage;
use App\Repository\StatusCoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

readonly class UpdateStatusCoursService
{
    // Délai d'archivage en jours
    public const DELAI_ARCHIVE = 30;

    public function __construct(
        private StatusCoursRepository $statusCoursRepository,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function update(Cours $cours): void
    {
        $dateNow = new \DateTimeImmutable();
        switch ($cours->getStatusCours()->getLibelle()) {
            // Passer le cours à l'état "En cours" et planifier un message pour le passer à l'état "Passé" à la fin du cours
            case StatusCoursEnum::OUVERT->value:
            case StatusCoursEnum::COMPLET->value:
                if ($cours->getDateCours()->format('Y-m-d H:i') === $dateNow->format('Y-m-d H:i')) {
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
                }
                break;

                // Passer le cours à l'état "Passé" et planifier un message pour le passer à l'état "Archive" à la fin du cours
            case StatusCoursEnum::EN_COURS->value:
                $dateCoursWithDuration = $cours->getDateCours()->modify('+'.$cours->getDuree().' minutes');
                if ($dateCoursWithDuration->format('Y-m-d H:i') === $dateNow->format('Y-m-d H:i')) {
                    $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::PASSE->value]));
                    $this->entityManager->persist($cours);
                    $this->entityManager->flush();
                    // Fixer le délai à 30 jours - durée du cours en millisecondes
                    $delay = self::DELAI_ARCHIVE * 24 * 60 * 60 * 1000 - ($cours->getDuree() * 60 * 1000);
                    $this->messageBus->dispatch(
                        new UpdateStatusCoursMessage(
                            $cours->getId()),
                        [new DelayStamp($delay)]
                    );
                }
                break;

                // Passer le cours à l'état "Archive"
            case StatusCoursEnum::PASSE->value:
                $dateCoursWithDurationAndTimeAfter = $cours->getDateCours()->modify('+'.self::DELAI_ARCHIVE * 24 * 60 .' minutes');
                if ($dateCoursWithDurationAndTimeAfter->format('Y-m-d H:i') === $dateNow->format('Y-m-d H:i')) {
                    $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ARCHIVE->value]));
                    $this->entityManager->persist($cours);
                    $this->entityManager->flush();
                }
                break;
        }
    }
}
