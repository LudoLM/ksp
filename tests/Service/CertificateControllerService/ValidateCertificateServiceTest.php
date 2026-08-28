<?php

namespace App\Tests\Service\CertificateControllerService;

use App\Entity\CertificatMedical;
use App\Entity\User;
use App\Enum\StatusCertificateEnum;
use App\Message\SendCertificateStatusEmailMessage;
use App\Service\CertificateControllerService\ValidateCertificateService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
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
    private FilesystemOperator&MockObject $filesystem;
    private ValidateCertificateService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->filesystem = $this->createMock(FilesystemOperator::class);
        $this->service = new ValidateCertificateService($this->em, $this->messageBus, $this->filesystem);

        $this->messageBus->method('dispatch')->willReturn(new Envelope(new \stdClass()));
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

    private function createUser(): User
    {
        $user = new User();
        $user->email = 'jean@example.com';
        $user->setPassword('hashed_password');
        $user->setPrenom('Jean');
        $user->setNom('Dupont');

        return $user;
    }

    #[DataProvider('actionProvider')]
    public function testUpdateStatusSetsExpectedStateAndDispatchesEmail(
        string $action,
        ?string $reason,
        ?\DateTimeImmutable $validUntil,
        string $expectedStatus,
        ?\DateTimeImmutable $expectedValidUntil,
        ?string $expectedReason,
    ): void {
        $certificate = $this->createCertificate(1);

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('flush');
        $this->em->expects($this->once())->method('commit');

        $dispatchedMessage = null;
        $this->messageBus->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessage): Envelope {
                $dispatchedMessage = $message;

                return new Envelope($message);
            });

        $this->service->updateStatus($certificate, $action, $reason, $validUntil);

        $this->assertSame($expectedStatus, $certificate->getStatus());
        $this->assertSame($expectedValidUntil, $certificate->getValidUntil());
        $this->assertSame($expectedReason, $certificate->getRejectionReason());
        $this->assertInstanceOf(SendCertificateStatusEmailMessage::class, $dispatchedMessage);
        $this->assertSame(1, $dispatchedMessage->getCertificateId());
    }

    public static function actionProvider(): \Generator
    {
        $validUntil = new \DateTimeImmutable('+6 months');

        yield 'approve' => [
            'action' => 'approve',
            'reason' => null,
            'validUntil' => $validUntil,
            'expectedStatus' => StatusCertificateEnum::APPROVED->value,
            'expectedValidUntil' => $validUntil,
            'expectedReason' => null,
        ];

        yield 'reject' => [
            'action' => 'reject',
            'reason' => 'Document illisible',
            'validUntil' => null,
            'expectedStatus' => StatusCertificateEnum::REJECTED->value,
            'expectedValidUntil' => null,
            'expectedReason' => 'Document illisible',
        ];
    }

    public function testApproveRemovesThePreviousApprovedCertificateAndItsFile(): void
    {
        $user = $this->createUser();

        $previousApproved = new CertificatMedical();
        $previousApproved->setCertificateFilename('old-approved.pdf');
        $previousApproved->setUploadedAt(new \DateTimeImmutable());
        $previousApproved->setStatus(StatusCertificateEnum::APPROVED->value);
        $user->addCertificatMedical($previousApproved);

        $newCertificate = $this->createCertificate(2);
        $user->addCertificatMedical($newCertificate);

        $this->filesystem->expects($this->once())->method('delete')->with('old-approved.pdf');
        $this->em->expects($this->once())->method('remove')->with($previousApproved);

        $this->service->updateStatus($newCertificate, 'approve', validUntil: new \DateTimeImmutable('+1 year'));

        $this->assertSame(StatusCertificateEnum::APPROVED->value, $newCertificate->getStatus());
    }

    public function testApproveDeletesThePreviousFileOnlyAfterTheTransactionCommits(): void
    {
        $user = $this->createUser();

        $previousApproved = new CertificatMedical();
        $previousApproved->setCertificateFilename('old-approved.pdf');
        $previousApproved->setUploadedAt(new \DateTimeImmutable());
        $previousApproved->setStatus(StatusCertificateEnum::APPROVED->value);
        $user->addCertificatMedical($previousApproved);

        $newCertificate = $this->createCertificate(2);
        $user->addCertificatMedical($newCertificate);

        $calls = [];
        $this->em->method('commit')->willReturnCallback(function () use (&$calls): void {
            $calls[] = 'commit';
        });
        $this->filesystem->method('delete')->willReturnCallback(function () use (&$calls): void {
            $calls[] = 'delete';
        });

        $this->service->updateStatus($newCertificate, 'approve', validUntil: new \DateTimeImmutable('+1 year'));

        $this->assertSame(['commit', 'delete'], $calls);
    }

    public function testApproveDoesNotDeleteThePreviousFileWhenTheFlushFails(): void
    {
        $user = $this->createUser();

        $previousApproved = new CertificatMedical();
        $previousApproved->setCertificateFilename('old-approved.pdf');
        $previousApproved->setUploadedAt(new \DateTimeImmutable());
        $previousApproved->setStatus(StatusCertificateEnum::APPROVED->value);
        $user->addCertificatMedical($previousApproved);

        $newCertificate = $this->createCertificate(2);
        $user->addCertificatMedical($newCertificate);

        $this->em->method('flush')->willThrowException(new \RuntimeException('DB down'));
        $this->em->expects($this->once())->method('rollback');
        $this->em->expects($this->never())->method('commit');
        $this->filesystem->expects($this->never())->method('delete');

        $this->expectException(\RuntimeException::class);

        $this->service->updateStatus($newCertificate, 'approve', validUntil: new \DateTimeImmutable('+1 year'));
    }

    public function testRejectDoesNotTouchAPreviousApprovedCertificate(): void
    {
        $user = $this->createUser();

        $previousApproved = new CertificatMedical();
        $previousApproved->setCertificateFilename('old-approved.pdf');
        $previousApproved->setUploadedAt(new \DateTimeImmutable());
        $previousApproved->setStatus(StatusCertificateEnum::APPROVED->value);
        $user->addCertificatMedical($previousApproved);

        $newCertificate = $this->createCertificate(2);
        $user->addCertificatMedical($newCertificate);

        $this->filesystem->expects($this->never())->method('delete');
        $this->em->expects($this->never())->method('remove');

        $this->service->updateStatus($newCertificate, 'reject', 'Document illisible');

        $this->assertSame(StatusCertificateEnum::APPROVED->value, $previousApproved->getStatus());
    }
}
