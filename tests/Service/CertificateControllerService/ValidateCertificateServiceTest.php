<?php

namespace App\Tests\Service\CertificateControllerService;

use App\Entity\CertificatMedical;
use App\Enum\StatusCertificateEnum;
use App\Message\SendCertificateStatusEmailMessage;
use App\Service\CertificateControllerService\ValidateCertificateService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(ValidateCertificateService::class)]
class ValidateCertificateServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private MessageBusInterface&MockObject $messageBus;
    private ValidateCertificateService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->service = new ValidateCertificateService($this->em, $this->messageBus);
    }

    private function createCertificate(int $id): CertificatMedical
    {
        $certificate = new CertificatMedical();
        $certificate->setCertificateFilename('certificate.pdf');
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setStatus(StatusCertificateEnum::PENDING->value);

        $idProp = new \ReflectionClass($certificate)->getProperty('id');
        $idProp->setValue($certificate, $id);

        return $certificate;
    }

    #[DataProvider('actionProvider')]
    public function testUpdateStatusSetsExpectedStateAndDispatchesEmail(
        string $action,
        ?string $reason,
        string $expectedStatus,
        bool $expectsValidUntil,
        ?string $expectedReason,
    ): void {
        $certificate = $this->createCertificate(1);

        $this->em->expects($this->once())->method('flush');

        $dispatchedMessage = null;
        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessage): Envelope {
                $dispatchedMessage = $message;

                return new Envelope($message);
            });

        $this->service->updateStatus($certificate, $action, $reason);

        $this->assertSame($expectedStatus, $certificate->getStatus());
        $this->assertSame($expectsValidUntil, $certificate->getValidUntil() instanceof \DateTimeImmutable);
        $this->assertSame($expectedReason, $certificate->getRejectionReason());
        $this->assertInstanceOf(SendCertificateStatusEmailMessage::class, $dispatchedMessage);
        $this->assertSame(1, $dispatchedMessage->getCertificateId());
    }

    public static function actionProvider(): \Generator
    {
        yield 'approve' => [
            'action' => 'approve',
            'reason' => null,
            'expectedStatus' => StatusCertificateEnum::APPROVED->value,
            'expectsValidUntil' => true,
            'expectedReason' => null,
        ];

        yield 'reject' => [
            'action' => 'reject',
            'reason' => 'Document illisible',
            'expectedStatus' => StatusCertificateEnum::REJECTED->value,
            'expectsValidUntil' => false,
            'expectedReason' => 'Document illisible',
        ];
    }
}
