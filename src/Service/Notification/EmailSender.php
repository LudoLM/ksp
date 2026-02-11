<?php

namespace App\Service\Notification;

use App\Service\Interface\Notification\NotificationInterface;
use App\Service\Interface\Notification\NotificationSenderInterface;
use App\Service\Interface\Notification\RecipientInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

readonly class EmailSender implements NotificationSenderInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromEmail,
    ) {
    }

    public function send(NotificationInterface $notification, RecipientInterface $recipient): void
    {
        $recipientEmail = $recipient->getEmail();
        if (null === $recipientEmail || '' === $recipientEmail) {
            throw new \InvalidArgumentException('L\'adresse email du destinataire est requise pour une notification email.');
        }

        $email = new TemplatedEmail()
            ->from($this->fromEmail)
            ->to($recipientEmail)
            ->subject($notification->getSubject())
            ->context($notification->getParameters());

        if (null !== $notification->getTemplate() && '' !== $notification->getTemplate()) {
            $email->htmlTemplate($notification->getTemplate());
        } elseif ('' !== $notification->getContent()) {
            $email->html($notification->getContent());
        }

        $this->mailer->send($email);
    }

    public function supports(NotificationInterface $notification): bool
    {
        return 'email' === $notification->getType();
    }
}
