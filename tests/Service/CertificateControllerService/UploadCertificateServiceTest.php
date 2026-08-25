<?php

namespace App\Tests\Service\CertificateControllerService;

use App\Entity\CertificatMedical;
use App\Entity\User;
use App\Enum\StatusCertificateEnum;
use App\Service\CertificateControllerService\UploadCertificateService;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(UploadCertificateService::class)]
class UploadCertificateServiceTest extends TestCase
{
    private FilesystemOperator&MockObject $filesystem;
    private EntityManagerInterface&MockObject $em;
    private LoggerInterface&MockObject $logger;
    private UploadCertificateService $service;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(FilesystemOperator::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new UploadCertificateService($this->filesystem, $this->em, $this->logger);
    }

    private function createUploadedFile(string $mimeType, string $extension, int $size): UploadedFile&MockObject
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getMimeType')->willReturn($mimeType);
        $file->method('getClientOriginalExtension')->willReturn($extension);
        $file->method('getSize')->willReturn($size);

        return $file;
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

    public function testValidateAcceptsAPdfUnderSizeLimit(): void
    {
        $file = $this->createUploadedFile('application/pdf', 'pdf', 1024);

        $this->service->validate($file);

        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidFileProvider')]
    public function testValidateRejectsInvalidFile(string $mimeType, string $extension, int $size, string $expectedMessage): void
    {
        $file = $this->createUploadedFile($mimeType, $extension, $size);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->service->validate($file);
    }

    public static function invalidFileProvider(): \Generator
    {
        yield 'wrong_mime_type' => [
            'mimeType' => 'image/png',
            'extension' => 'pdf',
            'size' => 1024,
            'expectedMessage' => 'Seuls les fichiers PDF sont acceptés.',
        ];

        yield 'wrong_extension' => [
            'mimeType' => 'application/pdf',
            'extension' => 'png',
            'size' => 1024,
            'expectedMessage' => 'Seuls les fichiers PDF sont acceptés.',
        ];

        yield 'too_large' => [
            'mimeType' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 6 * 1024 * 1024,
            'expectedMessage' => 'Le fichier ne doit pas dépasser 5 Mo.',
        ];
    }

    public function testUploadPersistsNewCertificateWhenUserHasNone(): void
    {
        $user = $this->createUser();
        $file = $this->createUploadedFile('application/pdf', 'pdf', 1024);
        $file->method('getContent')->willReturn('%PDF-1.4 fake content');

        $writtenFilename = null;
        $this->filesystem->expects($this->once())
            ->method('write')
            ->willReturnCallback(function ($filename, $content) use (&$writtenFilename): void {
                $writtenFilename = $filename;
                $this->assertSame('%PDF-1.4 fake content', $content);
            });
        $this->filesystem->expects($this->never())->method('delete');

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(CertificatMedical::class));
        $this->em->expects($this->once())->method('flush');
        $this->em->expects($this->once())->method('commit');
        $this->em->expects($this->never())->method('rollback');

        $certificate = $this->service->upload($user, $file);

        $this->assertSame($user, $certificate->getUser());
        $this->assertSame(StatusCertificateEnum::PENDING->value, $certificate->getStatus());
        $this->assertStringEndsWith('.pdf', $certificate->getCertificateFilename());
        $this->assertSame($writtenFilename, $certificate->getCertificateFilename());
    }

    public function testUploadReplacesExistingCertificateAndDeletesOldFile(): void
    {
        $user = $this->createUser();

        $existingCertificate = new CertificatMedical();
        $existingCertificate->setUser($user);
        $existingCertificate->setCertificateFilename('old-certificate.pdf');
        $existingCertificate->setUploadedAt(new \DateTimeImmutable());
        $existingCertificate->setStatus(StatusCertificateEnum::APPROVED->value);
        $user->setCertificatMedical($existingCertificate);

        $file = $this->createUploadedFile('application/pdf', 'pdf', 1024);
        $file->method('getContent')->willReturn('new content');

        $this->filesystem->expects($this->once())->method('write');
        $this->filesystem->expects($this->once())
            ->method('delete')
            ->with('old-certificate.pdf');

        $this->em->expects($this->once())->method('remove')->with($existingCertificate);
        $this->em->expects($this->exactly(2))->method('flush');
        $this->em->expects($this->once())->method('commit');

        $certificate = $this->service->upload($user, $file);

        $this->assertNotSame('old-certificate.pdf', $certificate->getCertificateFilename());
    }

    public function testUploadRollsBackAndCleansUpNewFileWhenFlushFails(): void
    {
        $user = $this->createUser();
        $file = $this->createUploadedFile('application/pdf', 'pdf', 1024);
        $file->method('getContent')->willReturn('content');

        $writtenFilename = null;
        $this->filesystem->method('write')
            ->willReturnCallback(function ($filename) use (&$writtenFilename): void {
                $writtenFilename = $filename;
            });

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->method('flush')->willThrowException(new \RuntimeException('DB down'));
        $this->em->expects($this->once())->method('rollback');
        $this->em->expects($this->never())->method('commit');

        $this->filesystem->expects($this->once())
            ->method('delete')
            ->willReturnCallback(function ($filename) use (&$writtenFilename): void {
                $this->assertSame($writtenFilename, $filename);
            });

        $this->logger->expects($this->once())->method('error');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB down');

        $this->service->upload($user, $file);
    }
}
