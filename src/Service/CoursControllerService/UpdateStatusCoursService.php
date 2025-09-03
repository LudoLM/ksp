<?php

namespace App\Service\CoursControllerService;

use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Helper\UpdateStatusCoursHelper;
use App\Message\UpdateStatusCoursMessage;
use App\Repository\StatusCoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class UpdateStatusCoursService
{
    public const DELAI_ARCHIVE = 30;

    public function __construct(
        private readonly UpdateStatusCoursHelper $updateStatusCoursHelper,
        private readonly MessageBusInterface $messageBus,
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function updateToEnCours(Cours $cours): void
    {
        // Fixer le délai à la durée du cours
        $delay = $cours->getDuree() * 60 * 1000;
        $this->updateStatusCoursHelper->checkCoursUpdatable($cours, StatusCoursEnum::EN_COURS, $cours->getDateImmutable(), $delay);
    }

    public function updateToPasse(Cours $cours): void
    {
        $dateCours = $cours->getDateCours();
        // Convertir la date du cours en objet DateTimeImmutable
        if ($dateCours instanceof \DateTime) {
            $dateCours = \DateTimeImmutable::createFromMutable($dateCours);
        }
        $dateCoursWithDuration = $cours->getDateImmutable()->modify('+'.$cours->getDuree().' minutes');
        // Fixer le délai à 30 jours - durée du cours en millisecondes
        $delay = self::DELAI_ARCHIVE * 24 * 60 * 60 * 1000 - ($cours->getDuree() * 60 * 1000);
        $this->updateStatusCoursHelper->checkCoursUpdatable($cours, StatusCoursEnum::PASSE, $dateCoursWithDuration, $delay);
    }

    public function updateToArchive(Cours $cours): void
    {
        $dateCoursWithDurationAndTimeAfter = $cours->getDateImmutable()->modify('+'.self::DELAI_ARCHIVE * 24 * 60 .' minutes');
        $this->updateStatusCoursHelper->checkCoursUpdatable($cours, StatusCoursEnum::ARCHIVE, $dateCoursWithDurationAndTimeAfter, 0);
    }

    public function dispatchCoursStatusUpdate(Cours $cours): void
    {
        // Obtenez la date du cours
        $dateCours = $cours->getDateCours();

        $currentDateTime = new \DateTime('now');

        // Calculez le délai en millisecondes
        $delay = ($dateCours->getTimestamp() - $currentDateTime->getTimestamp()) * 1000;

        $this->messageBus->dispatch(
            new UpdateStatusCoursMessage(
                $cours->getId()),
            [new DelayStamp($delay)]
        );
    }

    public function prepareAndLaunchCours(Cours $cours): void
    {
        $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));
        $cours->setLaunchedAt(new \DateTime('now'));

        $this->dispatchCoursStatusUpdate($cours);

        $this->em->persist($cours);
    }
}
