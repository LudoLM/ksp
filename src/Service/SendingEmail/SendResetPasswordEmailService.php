<?php

namespace App\Service\SendingEmail;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

readonly class SendResetPasswordEmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $baseUrl,
    ) {
    }

    public function send(User $user, string $token): void
    {
        $email = (new TemplatedEmail())
            ->from('ludolemelinaire@gmail.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('emails/resetPassword.html.twig')
            ->locale('fr')
            ->context([
                'user' => $user,
                'token' => $token,
                'url' => $this->baseUrl,
            ]);
        $this->mailer->send($email);
    }
}
