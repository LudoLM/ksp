<?php

declare(strict_types=1);

namespace App\Tests\Service\SendingEmail;

use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\User;
use App\Enum\StatusCoursWishesFormEnum;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;
use App\Service\SendingEmail\SendCoursWishesFormStatusEmailService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SendCoursWishesFormStatusEmailService::class)]
class SendCoursWishesFormStatusEmailServiceTest extends TestCase
{
    private NotificationManager&MockObject $notificationManager;
    private SendCoursWishesFormStatusEmailService $service;

    protected function setUp(): void
    {
        $this->notificationManager = $this->createMock(NotificationManager::class);
        $this->service = new SendCoursWishesFormStatusEmailService($this->notificationManager, 'https://kine-sport-sante.fr');
    }

    private function createForm(string $status): CoursWishesForm
    {
        $form = new CoursWishesForm();
        $form->setEmail('jean@example.com');
        $form->setSaison('2026-2027');
        $form->setPackSouhaite(new Pack());
        $form->setModeReglement('CbComptant');
        $form->setStatus($status);
        $form->setSubmittedAt(new \DateTimeImmutable());

        return $form;
    }

    private function captureNotificationAndRecipient(CoursWishesForm $form, ?string $registrationToken = null): array
    {
        $capturedNotification = null;
        $capturedRecipient = null;

        $this->notificationManager
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($notification, $recipient) use (&$capturedNotification, &$capturedRecipient): void {
                $capturedNotification = $notification;
                $capturedRecipient = $recipient;
            });

        $this->service->send($form, $registrationToken);

        return [$capturedNotification, $capturedRecipient];
    }

    public function testSendForAnAnonymousApprovedFormIncludesTheRegisterLink(): void
    {
        $form = $this->createForm(StatusCoursWishesFormEnum::VALIDE->value);

        [$notification, $recipient] = $this->captureNotificationAndRecipient($form, 'raw-registration-token');

        $this->assertInstanceOf(EmailNotification::class, $notification);
        $this->assertSame('emails/coursWishesFormApproved.html.twig', $notification->getTemplate());
        $this->assertSame($form, $notification->getParameters()['form']);
        $this->assertSame('https://kine-sport-sante.fr/register?token=raw-registration-token', $notification->getParameters()['registerUrl']);
        $this->assertSame($form, $recipient);
    }

    public function testSendForAnApprovedFormWithNoTokenOmitsTheRegisterLink(): void
    {
        $form = $this->createForm(StatusCoursWishesFormEnum::VALIDE->value);
        $form->setUser(new User());

        [$notification] = $this->captureNotificationAndRecipient($form);

        $this->assertNull($notification->getParameters()['registerUrl']);
    }

    public function testSendForAFormNeedingCorrectionIncludesTheReasonAndFormLink(): void
    {
        $form = $this->createForm(StatusCoursWishesFormEnum::A_CORRIGER->value);
        $form->setCorrectionReason('Créneau primaire déjà complet');

        [$notification] = $this->captureNotificationAndRecipient($form);

        $this->assertSame('emails/coursWishesFormCorrection.html.twig', $notification->getTemplate());
        $this->assertSame('Créneau primaire déjà complet', $notification->getParameters()['reason']);
        $this->assertSame('https://kine-sport-sante.fr/mon-dossier-inscription', $notification->getParameters()['formUrl']);
    }

    public function testSendPropagatesExceptionFromNotificationManager(): void
    {
        $form = $this->createForm(StatusCoursWishesFormEnum::VALIDE->value);

        $this->notificationManager
            ->method('send')
            ->willThrowException(new \RuntimeException('Aucun expéditeur trouvé'));

        $this->expectException(\RuntimeException::class);

        $this->service->send($form);
    }
}
