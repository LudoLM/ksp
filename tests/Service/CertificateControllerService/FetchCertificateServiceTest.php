<?php

namespace App\Tests\Service\CertificateControllerService;

use App\Entity\CertificatMedical;
use App\Entity\User;
use App\Enum\StatusCertificateEnum;
use App\Repository\CertificatMedicalRepository;
use App\Service\CertificateControllerService\FetchCertificateService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(FetchCertificateService::class)]
class FetchCertificateServiceTest extends TestCase
{
    private FilesystemOperator&MockObject $filesystem;
    private CertificatMedicalRepository&MockObject $repository;
    private FetchCertificateService $service;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(FilesystemOperator::class);
        $this->repository = $this->createMock(CertificatMedicalRepository::class);
        $this->service = new FetchCertificateService($this->filesystem, $this->repository);
    }

    private function createUser(int $id): User
    {
        $user = new User();
        $user->email = 'jean@example.com';
        $user->setPassword('hashed_password');
        $user->setPrenom('Jean');
        $user->setNom('Dupont');

        $idProp = new \ReflectionClass($user)->getProperty('id');
        $idProp->setValue($user, $id);

        return $user;
    }

    private function createCertificate(int $id, User $user, \DateTimeImmutable $uploadedAt): CertificatMedical
    {
        $certificate = new CertificatMedical();
        $certificate->setUser($user);
        $certificate->setCertificateFilename("certificate-{$id}.pdf");
        $certificate->setUploadedAt($uploadedAt);
        $certificate->setStatus(StatusCertificateEnum::PENDING->value);

        $idProp = new \ReflectionClass($certificate)->getProperty('id');
        $idProp->setValue($certificate, $id);

        return $certificate;
    }

    public function testGetPendingCertificatesFormatsDataAndMetadata(): void
    {
        $user = $this->createUser(1);
        $uploadedAt = new \DateTimeImmutable('2026-01-15 10:00:00');
        $certificate = $this->createCertificate(10, $user, $uploadedAt);

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('count')->willReturn(1);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$certificate]));

        $this->repository->expects($this->once())
            ->method('paginatePending')
            ->with(1, 15)
            ->willReturn($paginator);

        $result = $this->service->getPendingCertificates(1, 15);

        $this->assertSame([
            'total_items' => 1,
            'current_page' => 1,
            'total_pages' => 1,
        ], $result['metadata']);

        $this->assertCount(1, $result['data']);
        $this->assertSame([
            'id' => 10,
            'status' => StatusCertificateEnum::PENDING->value,
            'uploadedAt' => '2026-01-15 10:00:00',
            'validUntil' => null,
            'certificateFilename' => 'certificate-10.pdf',
            'user' => [
                'id' => 1,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean@example.com',
            ],
        ], $result['data'][0]);
    }

    #[DataProvider('totalPagesProvider')]
    public function testGetPendingCertificatesComputesTotalPages(int $totalItems, int $limit, int $expectedTotalPages): void
    {
        $paginator = $this->createMock(Paginator::class);
        $paginator->method('count')->willReturn($totalItems);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([]));

        $this->repository->method('paginatePending')->willReturn($paginator);

        $result = $this->service->getPendingCertificates(1, $limit);

        $this->assertSame($expectedTotalPages, $result['metadata']['total_pages']);
    }

    public static function totalPagesProvider(): \Generator
    {
        yield 'exact_multiple' => ['totalItems' => 30, 'limit' => 15, 'expectedTotalPages' => 2];
        yield 'with_remainder' => ['totalItems' => 31, 'limit' => 15, 'expectedTotalPages' => 3];
        yield 'single_page' => ['totalItems' => 5, 'limit' => 15, 'expectedTotalPages' => 1];
        yield 'empty' => ['totalItems' => 0, 'limit' => 15, 'expectedTotalPages' => 0];
    }

    public function testReadCertificateContentReturnsFileContent(): void
    {
        $certificate = $this->createCertificate(1, $this->createUser(1), new \DateTimeImmutable());

        $this->filesystem->method('fileExists')->with('certificate-1.pdf')->willReturn(true);
        $this->filesystem->method('read')->with('certificate-1.pdf')->willReturn('%PDF-1.4 binary content');

        $content = $this->service->readCertificateContent($certificate);

        $this->assertSame('%PDF-1.4 binary content', $content);
    }

    public function testReadCertificateContentThrowsWhenFileMissing(): void
    {
        $certificate = $this->createCertificate(1, $this->createUser(1), new \DateTimeImmutable());

        $this->filesystem->method('fileExists')->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fichier introuvable.');

        $this->service->readCertificateContent($certificate);
    }

    private function certificateWithStatus(string $status): CertificatMedical
    {
        $certificate = new CertificatMedical();
        $certificate->setCertificateFilename('certificate.pdf');
        $certificate->setUploadedAt(new \DateTimeImmutable());
        $certificate->setStatus($status);

        return $certificate;
    }

    public function testSelectCurrentCertificateReturnsNullForAnEmptyCollection(): void
    {
        $this->assertNull(FetchCertificateService::selectCurrentCertificate([]));
    }

    #[DataProvider('selectCurrentCertificateProvider')]
    public function testSelectCurrentCertificatePrioritizesPendingThenApprovedThenRejected(array $statuses, string $expectedStatus): void
    {
        $certificates = array_map($this->certificateWithStatus(...), $statuses);

        $selected = FetchCertificateService::selectCurrentCertificate($certificates);

        $this->assertNotNull($selected);
        $this->assertSame($expectedStatus, $selected->getStatus());
    }

    public static function selectCurrentCertificateProvider(): \Generator
    {
        yield 'pending_wins_over_approved' => [
            'statuses' => [StatusCertificateEnum::APPROVED->value, StatusCertificateEnum::PENDING->value],
            'expectedStatus' => StatusCertificateEnum::PENDING->value,
        ];

        yield 'pending_wins_over_rejected' => [
            'statuses' => [StatusCertificateEnum::REJECTED->value, StatusCertificateEnum::PENDING->value],
            'expectedStatus' => StatusCertificateEnum::PENDING->value,
        ];

        yield 'approved_wins_over_rejected_when_no_pending' => [
            'statuses' => [StatusCertificateEnum::REJECTED->value, StatusCertificateEnum::APPROVED->value],
            'expectedStatus' => StatusCertificateEnum::APPROVED->value,
        ];

        yield 'rejected_alone' => [
            'statuses' => [StatusCertificateEnum::REJECTED->value],
            'expectedStatus' => StatusCertificateEnum::REJECTED->value,
        ];
    }
}
