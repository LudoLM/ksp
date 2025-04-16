<?php

namespace App\Service\SendingEmail;

use App\Entity\User;
use App\Entity\UsersCours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

readonly class SendUpdateCoursEmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $baseUrl,
    ) {
    }

    public function send(UsersCours $usersCours, User $currentUser, \DateTimeInterface $initialDate, int $initialDuration): void
    {
        $email = (new TemplatedEmail())
            ->from('ludolemelinaire@gmail.com')
            ->to($usersCours->getUser()->getEmail())
            ->subject('Modification de cours')
            ->htmlTemplate('emails/updateCours.html.twig')
            ->locale('fr')
            ->context([
                'cours' => $usersCours->getCours(),
                'participant' => $usersCours->getUser(),
                'user' => $currentUser->getPrenom().' '.$currentUser->getNom(),
                'url' => $this->baseUrl,
                'initialDate' => $initialDate,
                'initialDuration' => $initialDuration,
            ]);
        $this->mailer->send($email);
    }
}
