<?php

namespace App\Service\SendingEmail;

use App\Entity\UsersCours;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;

readonly class SendCancelEmailService
{
    public function __construct(
        private NotificationManager $notificationManager,
        private EntityManagerInterface $em,
    ) {
    }

    public function send(UsersCours $usersCours): void
    {
        $notification = new EmailNotification(
            subject: 'Annulation du cours',
            content: 'Votre cours a été annulé',
            template: 'emails/cancel.html.twig',
            parameters: [
                'cours' => $usersCours->getCours(),
                'participant' => $usersCours->getUser(),
            ]
        );

        $this->notificationManager->send($notification, $usersCours->getUser());

        $usersCours->getUser()->setNombreCours($usersCours->getUser()->getNombreCours() + 1);
        $this->em->persist($usersCours->getUser());
    }
}
