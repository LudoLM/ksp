<?php

namespace App\Service\CoursControllerService;

use App\Entity\Cours;
use App\Message\SendUpdateCoursEmailMessage;
use App\Message\UpdateStatusCoursMessage;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

readonly class ActionsModifyOpenedCoursService
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private Security $security,
    ) {
    }

    public function handle(Cours $cours, int $initalDuration, \DateTimeInterface $initialDate): void
    {
        $currentDateTime = new \DateTime('now');
        // Création d'un nouveau message
        // Calcul le delai en millisecondes entre la date du cours et la date actuelle
        $delay = ($cours->getDateCours()->getTimestamp() - $currentDateTime->getTimestamp()) * 1000;

        // Si la date ou la durée du cours a changé, on recrée un message et on envoit des mails aux personnes inscrites
        if ($initalDuration !== $cours->getDuree() || $initialDate->getTimestamp() !== $cours->getDateCours()->getTimestamp()) {
            $this->messageBus->dispatch(
                new UpdateStatusCoursMessage(
                    $cours->getId()),
                [new DelayStamp($delay)]
            );

            // Envoi d'un mail aux personnes inscrites
            foreach ($cours->getUsersCours() as $usersCours) {
                $this->messageBus->dispatch(new SendUpdateCoursEmailMessage($usersCours->getId(), $this->security->getUser()->getId(), $initialDate, $initalDuration));
            }
        }
    }
}
