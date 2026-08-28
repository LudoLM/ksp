<?php

namespace App\Tests\Service;

use App\Entity\CertificatMedical;
use App\Enum\StatusCertificateEnum;
use App\Repository\CertificatMedicalRepository;
use App\Service\ExpireCertificatesService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(ExpireCertificatesService::class)]
class ExpireCertificatesServiceTest extends TestCase
{
    private CertificatMedicalRepository&MockObject $repository;
    private EntityManagerInterface&MockObject $em;
    private LoggerInterface&MockObject $logger;
    private FilesystemOperator&MockObject $filesystem;
    private ExpireCertificatesService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CertificatMedicalRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->filesystem = $this->createMock(FilesystemOperator::class);
        $this->service = new ExpireCertificatesService($this->repository, $this->em, $this->logger, $this->filesystem);
    }

    private function createApprovedCertificate(): CertificatMedical
    {
        $certificate = new CertificatMedical();
        $certificate->setCertificateFilename('certificate.pdf');
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setStatus(StatusCertificateEnum::APPROVED->value);
        $certificate->setValidUntil(new \DateTimeImmutable('-1 day'));

        return $certificate;
    }

    public function testExpireCertificatesSetsStatusAndFlushesWhenSomeAreFound(): void
    {
        $certificate = $this->createApprovedCertificate();

        $this->repository->expects($this->once())
            ->method('findExpirable')
            ->willReturn([$certificate]);

        $this->em->expects($this->once())->method('flush');
        $this->filesystem->expects($this->once())->method('delete')->with('certificate.pdf');

        $result = $this->service->expireCertificates();

        $this->assertSame(StatusCertificateEnum::EXPIRED->value, $certificate->getStatus());
        $this->assertSame(['expired' => 1], $result);
    }

    public function testExpireCertificatesDoesNotFlushWhenNoneAreFound(): void
    {
        $this->repository->expects($this->once())
            ->method('findExpirable')
            ->willReturn([]);

        $this->em->expects($this->never())->method('flush');
        $this->filesystem->expects($this->never())->method('delete');

        $result = $this->service->expireCertificates();

        $this->assertSame(['expired' => 0], $result);
    }

    public function testExpireCertificatesLogsAndContinuesWhenFileDeletionFails(): void
    {
        $certificate = $this->createApprovedCertificate();

        $this->repository->expects($this->once())
            ->method('findExpirable')
            ->willReturn([$certificate]);

        $this->filesystem->method('delete')->willThrowException(new \RuntimeException('storage down'));
        $this->logger->expects($this->once())->method('error');

        $result = $this->service->expireCertificates();

        $this->assertSame(StatusCertificateEnum::EXPIRED->value, $certificate->getStatus());
        $this->assertSame(['expired' => 1], $result);
    }
}
