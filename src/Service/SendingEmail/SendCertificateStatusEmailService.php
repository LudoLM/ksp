<?php

namespace App\Service\SendingEmail;

use App\Entity\CertificatMedical;
use App\Enum\StatusCertificateEnum;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;

readonly class SendCertificateStatusEmailService
{
    public function __construct(
        private NotificationManager $notificationManager,
    ) {
    }

    public function send(CertificatMedical $certificate): void
    {
        $user = $certificate->getUser();

        if (!$user instanceof \App\Entity\User) {
            return;
        }

        $notification = StatusCertificateEnum::APPROVED->value === $certificate->getStatus()
            ? new EmailNotification(
                subject: 'Votre certificat médical a été validé',
                content: 'Votre certificat médical a été validé',
                template: 'emails/certificateApproved.html.twig',
                parameters: [
                    'user' => $user,
                    'validUntil' => $certificate->getValidUntil(),
                ]
            )
            : new EmailNotification(
                subject: 'Votre certificat médical a été refusé',
                content: 'Votre certificat médical a été refusé',
                template: 'emails/certificateRejected.html.twig',
                parameters: [
                    'user' => $user,
                    'reason' => $certificate->getRejectionReason(),
                ]
            );

        $this->notificationManager->send($notification, $user);
    }
}
