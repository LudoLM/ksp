<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;

class SendCancelEmailService
{

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    public function send($usersCours, $currentUser) : void
    {

        $email = (new TemplatedEmail())
            ->from($currentUser->getEmail())
            ->to($usersCours->getUser()->getEmail())
            ->subject('Annulation du cours')
            ->htmlTemplate('emails/cancel.html.twig')
            ->locale('fr')
            ->context([
                'cours' => $usersCours->getCours(),
                'participant' => $usersCours->getUser(),
                'user' => $currentUser->getPrenom() . ' ' . $currentUser->getNom(),
            ]);
        $this->mailer->send($email);
        $usersCours->getUser()->setNombreCours($usersCours->getUser()->getNombreCours() + 1);
        $this->em->persist($usersCours->getUser());

    }

}
