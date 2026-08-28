<?php

namespace App\Tests\Command;

use App\Command\ExpireCertificatesCommand;
use App\Entity\CertificatMedical;
use App\Entity\User;
use App\Enum\StatusCertificateEnum;
use App\Repository\CertificatMedicalRepository;
use App\Service\ExpireCertificatesService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(ExpireCertificatesCommand::class)]
class ExpireCertificatesCommandTest extends TestCase
{
    private ExpireCertificatesService&MockObject $expireCertificatesService;
    private CertificatMedicalRepository&MockObject $repository;
    private EntityManagerInterface&MockObject $em;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->expireCertificatesService = $this->createMock(ExpireCertificatesService::class);
        $this->repository = $this->createMock(CertificatMedicalRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        $command = new ExpireCertificatesCommand($this->expireCertificatesService, $this->repository, $this->em);
        $this->commandTester = new CommandTester($command);
    }

    private function createApprovedCertificate(int $id): CertificatMedical
    {
        $user = new User();
        $user->email = 'jean@example.com';
        $user->setPassword('hashed_password');
        $user->setPrenom('Jean');
        $user->setNom('Dupont');

        $certificate = new CertificatMedical();
        $certificate->setUser($user);
        $certificate->setCertificateFilename('certificate.pdf');
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setStatus(StatusCertificateEnum::APPROVED->value);
        $certificate->setValidUntil(new \DateTimeImmutable('-1 day'));

        $idProp = new \ReflectionClass($certificate)->getProperty('id');
        $idProp->setValue($certificate, $id);

        return $certificate;
    }

    public function testDryRunDisplaysExpirableCertificatesWithoutModifyingAnything(): void
    {
        $this->repository->expects($this->once())
            ->method('findExpirable')
            ->willReturn([$this->createApprovedCertificate(1)]);

        $this->expireCertificatesService->expects($this->never())->method('expireCertificates');
        $this->em->expects($this->never())->method('beginTransaction');

        $exitCode = $this->commandTester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('jean@example.com', $this->commandTester->getDisplay());
    }

    public function testDryRunReportsWhenNothingToExpire(): void
    {
        $this->repository->expects($this->once())
            ->method('findExpirable')
            ->willReturn([]);

        $exitCode = $this->commandTester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Aucun certificat à expirer', $this->commandTester->getDisplay());
    }

    public function testExecuteExpiresCertificatesWithinATransaction(): void
    {
        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('commit');
        $this->em->expects($this->never())->method('rollback');

        $this->expireCertificatesService->expects($this->once())
            ->method('expireCertificates')
            ->willReturn(['expired' => 2]);

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('2 certificat(s) expiré(s)', $this->commandTester->getDisplay());
    }

    public function testExecuteRollsBackOnFailure(): void
    {
        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('rollback');
        $this->em->expects($this->never())->method('commit');

        $this->expireCertificatesService->method('expireCertificates')
            ->willThrowException(new \RuntimeException('DB down'));

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('DB down', $this->commandTester->getDisplay());
    }
}
