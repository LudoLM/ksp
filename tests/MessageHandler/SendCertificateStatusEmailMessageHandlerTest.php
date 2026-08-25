<?php

namespace App\Tests\MessageHandler;

use App\Entity\CertificatMedical;
use App\Enum\StatusCertificateEnum;
use App\Message\SendCertificateStatusEmailMessage;
use App\MessageHandler\SendCertificateStatusEmailMessageHandler;
use App\Repository\CertificatMedicalRepository;
use App\Service\SendingEmail\SendCertificateStatusEmailService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SendCertificateStatusEmailMessageHandler::class)]
class SendCertificateStatusEmailMessageHandlerTest extends TestCase
{
    private CertificatMedicalRepository&MockObject $repository;
    private SendCertificateStatusEmailService&MockObject $sendingService;
    private SendCertificateStatusEmailMessageHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CertificatMedicalRepository::class);
        $this->sendingService = $this->createMock(SendCertificateStatusEmailService::class);

        $this->handler = new SendCertificateStatusEmailMessageHandler(
            $this->sendingService,
            $this->repository,
        );
    }

    #[DataProvider('certificateStatusProvider')]
    public function testInvokeSendsEmailWhenCertificateExists(string $status): void
    {
        $certificate = new CertificatMedical();
        $certificate->setCertificateFilename('certificate.pdf');
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setStatus($status);

        $this->repository->expects($this->once())
            ->method('find')
            ->with(42)
            ->willReturn($certificate);

        $this->sendingService->expects($this->once())
            ->method('send')
            ->with($certificate);

        $this->handler->__invoke(new SendCertificateStatusEmailMessage(42));
    }

    public static function certificateStatusProvider(): \Generator
    {
        yield 'approved' => ['status' => StatusCertificateEnum::APPROVED->value];
        yield 'rejected' => ['status' => StatusCertificateEnum::REJECTED->value];
    }

    public function testInvokeThrowsExceptionWhenCertificateNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->with(404)
            ->willReturn(null);

        $this->sendingService->expects($this->never())
            ->method('send');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Certificat non trouvé');

        $this->handler->__invoke(new SendCertificateStatusEmailMessage(404));
    }

    public function testInvokePropagatesExceptionFromSendingService(): void
    {
        $certificate = new CertificatMedical();
        $certificate->setCertificateFilename('certificate.pdf');
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setStatus(StatusCertificateEnum::REJECTED->value);

        $this->repository->method('find')->willReturn($certificate);

        $this->sendingService
            ->method('send')
            ->willThrowException(new \RuntimeException('Aucun expéditeur trouvé'));

        $this->expectException(\RuntimeException::class);

        $this->handler->__invoke(new SendCertificateStatusEmailMessage(1));
    }
}
