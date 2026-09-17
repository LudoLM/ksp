<?php

declare(strict_types=1);

namespace App\Service\SendingEmail;

use App\Entity\CoursWishesForm;
use App\Enum\StatusCoursWishesFormEnum;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;

readonly class SendCoursWishesFormStatusEmailService
{
    public function __construct(
        private NotificationManager $notificationManager,
        private string $baseUrl,
    ) {
    }

    public function send(CoursWishesForm $form, ?string $registrationToken = null, ?string $correctionToken = null): void
    {
        $notification = StatusCoursWishesFormEnum::VALIDE->value === $form->getStatus()
            ? $this->buildApprovedNotification($form, $registrationToken)
            : new EmailNotification(
                subject: "Votre dossier d'inscription nécessite une correction",
                content: "Votre dossier d'inscription nécessite une correction",
                template: 'emails/coursWishesFormCorrection.html.twig',
                parameters: [
                    'form' => $form,
                    'reason' => $form->getCorrectionReason(),
                    'formUrl' => $this->baseUrl.'/demandeInscription'.(null !== $correctionToken ? '?token='.$correctionToken : ''),
                ]
            );

        $this->notificationManager->send($notification, $form);
    }

    private function buildApprovedNotification(CoursWishesForm $form, ?string $registrationToken): EmailNotification
    {
        return new EmailNotification(
            subject: "Votre dossier d'inscription est validé",
            content: "Votre dossier d'inscription est validé",
            template: 'emails/coursWishesFormApproved.html.twig',
            parameters: [
                'form' => $form,
                'registerUrl' => null !== $registrationToken ? $this->baseUrl.'/register?token='.$registrationToken : null,
                'registrationExpiresAt' => $form->getRegistrationTokenExpiresAt(),
                'formUrl' => $this->baseUrl.'/demandeInscription',
            ]
        );
    }
}
