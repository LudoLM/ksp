<?php

namespace App\Tests\Service\SendingEmail;

use App\Entity\CertificatMedical;
use App\Entity\User;
use App\Enum\StatusCertificateEnum;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;
use App\Service\SendingEmail\SendCertificateStatusEmailService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SendCertificateStatusEmailService::class)]
class SendCertificateStatusEmailServiceTest extends TestCase
{
    private NotificationManager&MockObject $notificationManager;
    private SendCertificateStatusEmailService $service;

    protected function setUp(): void
    {
        $this->notificationManager = $this->createMock(NotificationManager::class);
        $this->service = new SendCertificateStatusEmailService($this->notificationManager);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->email = 'jean@example.com';
        $user->setPassword('hashed_password');
        $user->setPrenom('Jean');
        $user->setNom('Dupont');

        return $user;
    }

    #[DataProvider('certificateStatusProvider')]
    public function testSendUsesTemplateMatchingCertificateStatus(
        string $status,
        ?\DateTimeImmutable $validUntil,
        ?string $rejectionReason,
        string $expectedTemplate,
    ): void {
        $user = $this->createUser();

        $certificate = new CertificatMedical();
        $certificate->setUser($user);
        $certificate->setCertificateFilename('certificate.pdf');
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setStatus($status);
        $certificate->setValidUntil($validUntil);
        $certificate->setRejectionReason($rejectionReason);

        $capturedNotification = null;
        $capturedRecipient = null;

        $this->notificationManager
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($notification, $recipient) use (&$capturedNotification, &$capturedRecipient): void {
                $capturedNotification = $notification;
                $capturedRecipient = $recipient;
            });

        $this->service->send($certificate);

        $this->assertInstanceOf(EmailNotification::class, $capturedNotification);
        $this->assertSame($expectedTemplate, $capturedNotification->getTemplate());
        $this->assertSame($user, $capturedNotification->getParameters()['user']);
        $this->assertSame($user, $capturedRecipient);

        if ($validUntil instanceof \DateTimeImmutable) {
            $this->assertSame($validUntil, $capturedNotification->getParameters()['validUntil']);
        }

        if (null !== $rejectionReason) {
            $this->assertSame($rejectionReason, $capturedNotification->getParameters()['reason']);
        }
    }

    public static function certificateStatusProvider(): \Generator
    {
        yield 'approved' => [
            'status' => StatusCertificateEnum::APPROVED->value,
            'validUntil' => new \DateTimeImmutable('+1 year'),
            'rejectionReason' => null,
            'expectedTemplate' => 'emails/certificateApproved.html.twig',
        ];

        yield 'rejected' => [
            'status' => StatusCertificateEnum::REJECTED->value,
            'validUntil' => null,
            'rejectionReason' => 'Document illisible',
            'expectedTemplate' => 'emails/certificateRejected.html.twig',
        ];
    }

    public function testSendDoesNothingWhenCertificateHasNoUser(): void
    {
        $certificate = new CertificatMedical();
        $certificate->setCertificateFilename('certificate.pdf');
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setStatus(StatusCertificateEnum::REJECTED->value);

        $this->notificationManager
            ->expects($this->never())
            ->method('send');

        $this->service->send($certificate);
    }

    public function testSendPropagatesExceptionFromNotificationManager(): void
    {
        $certificate = new CertificatMedical();
        $certificate->setUser($this->createUser());
        $certificate->setCertificateFilename('certificate.pdf');
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setStatus(StatusCertificateEnum::APPROVED->value);

        $this->notificationManager
            ->method('send')
            ->willThrowException(new \RuntimeException('Aucun expéditeur trouvé'));

        $this->expectException(\RuntimeException::class);

        $this->service->send($certificate);
    }
}
