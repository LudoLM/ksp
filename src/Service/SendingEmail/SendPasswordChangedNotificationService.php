<?php

namespace App\Service\SendingEmail;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

readonly class SendPasswordChangedNotificationService
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function send(User $user): void
    // TODO Change email
    {
        $email = new TemplatedEmail()
            ->from('ludolemelinaire@gmail.com')
            ->to($user->getEmail())
            ->subject('Votre mot de passe a été modifié')
            ->htmlTemplate('emails/passwordChanged.html.twig')
            ->locale('fr')
            ->context([
                'user' => $user,
                'changedAt' => new \DateTimeImmutable(),
            ]);
        $this->mailer->send($email);
    }
}
