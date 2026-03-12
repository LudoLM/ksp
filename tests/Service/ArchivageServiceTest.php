<?php

namespace App\Tests\Service;

use App\Constant\ArchivageConstants;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ArchivageService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ArchivageServiceTest extends TestCase
{
    private ArchivageService $archivageService;
    private \PHPUnit\Framework\MockObject\MockObject $userRepository;
    private \PHPUnit\Framework\MockObject\MockObject $em;
    private \PHPUnit\Framework\MockObject\MockObject $logger;

    public function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->archivageService = new ArchivageService($this->userRepository, $this->em, $this->logger);
    }

    /**
     * Test that archivage correctly identifies inactive users via validation.
     */
    #[DataProvider('inactiveUserProvider')]
    public function testArchivageValidatesCorrectCriteria(
        int $nombreCours,
        \DateTimeImmutable $lastVisit,
        bool $shouldValidate,
    ): void {
        // Arrange
        $user = $this->createTestUser(
            nombreCours: $nombreCours,
            lastVisit: $lastVisit
        );

        // Act & Assert
        try {
            // Use reflection to call the private validate method
            $reflection = new \ReflectionClass($this->archivageService);
            $method = $reflection->getMethod('validateCanArchive');
            $method->invoke($this->archivageService, $user, ArchivageConstants::MONTHS_INACTIVE_THRESHOLD);

            // If we reach here, validation passed
            $this->assertTrue($shouldValidate,
                "Validation should pass for: nombreCours={$nombreCours}, lastVisit={$lastVisit->format('Y-m-d')}");
        } catch (\InvalidArgumentException $e) {
            // Validation failed
            $this->assertFalse($shouldValidate,
                "Validation failed unexpectedly: {$e->getMessage()}");
        }
    }

    /**
     * Test archiveUser marks user as archived with timestamp.
     */
    public function testArchiveUserSetsArchivedStatus(): void
    {
        // Arrange
        $user = $this->createTestUser(nombreCours: 0, lastVisit: new \DateTimeImmutable('-12 months'));
        $this->em->expects($this->once())->method('persist');

        // Act
        $reflection = new \ReflectionClass($this->archivageService);
        $method = $reflection->getMethod('archiveUser');
        $method->invoke($this->archivageService, $user);

        // Assert
        $this->assertTrue($user->isArchived(), 'User should be marked as archived');
        $this->assertNotNull($user->getArchivedAt(), 'ArchivedAt timestamp should be set');
    }

    /**
     * Test unarchivage removes archive status.
     */
    public function testUnarchiveUserRemovesArchivedStatus(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $user->setIsArchived(true);
        $user->setArchivedAt(new \DateTime());
        $this->em->expects($this->once())->method('persist');

        // Act
        $this->archivageService->unarchiveUser($user);

        // Assert
        $this->assertFalse($user->isArchived(), 'User should no longer be archived');
        $this->assertNull($user->getArchivedAt(), 'ArchivedAt should be null');
    }

    /**
     * Test unarchiveUser idempotence - calling twice on non-archived returns early.
     */
    public function testUnarchiveUserIdempotence(): void
    {
        // Arrange
        $user = $this->createTestUser();
        $this->assertFalse($user->isArchived());
        $this->em->expects($this->never())->method('persist');

        // Act
        $this->archivageService->unarchiveUser($user);

        // Assert - persist not called when user not archived
    }

    /**
     * Test that archiveInactiveUsers returns correct structure.
     */
    public function testArchiveInactiveUsersReturnsResultArray(): void
    {
        // Arrange
        $inactiveUsers = [];
        $this->userRepository->expects($this->once())
            ->method('findInactiveUsers')
            ->willReturn($inactiveUsers);
        $this->em->expects($this->never())->method('flush'); // Empty result, no flush

        // Act
        $result = $this->archivageService->archiveInactiveUsers();

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('archived', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals(0, $result['archived']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test that validation errors are collected.
     */
    public function testArchiveInactiveUsersCollectsValidationErrors(): void
    {
        // Arrange
        $userWithCourses = $this->createTestUser(nombreCours: 3); // Invalid - has courses
        $this->userRepository->expects($this->once())
            ->method('findInactiveUsers')
            ->willReturn([$userWithCourses]);
        // When there are errors, flush is not called
        $this->em->expects($this->never())->method('flush');

        // Act
        $result = $this->archivageService->archiveInactiveUsers();

        // Assert
        $this->assertEquals(0, $result['archived']);
        $this->assertGreaterThan(0, count($result['errors']), 'Should have validation error');
    }

    /**
     * Test logging happens on successful archivage.
     */
    public function testArchiveInactiveUsersLogsSuccessfully(): void
    {
        // Arrange - Create a valid inactive user (older than 12 months threshold)
        $user = $this->createTestUser(nombreCours: 0, lastVisit: new \DateTimeImmutable('-13 months'));
        $this->userRepository->expects($this->once())
            ->method('findInactiveUsers')
            ->willReturn([$user]);
        $this->em->expects($this->once())->method('flush');
        // Expect 2 info calls: 1 from archiveUser(), 1 from archiveInactiveUsers()
        $this->logger->expects($this->exactly(2))->method('info');

        // Act
        $result = $this->archivageService->archiveInactiveUsers();

        // Assert - 1 user archived
        $this->assertEquals(1, $result['archived']);
    }

    /**
     * Data provider for validation criteria tests.
     */
    public static function inactiveUserProvider(): \Generator
    {
        // Should validate: 0 courses, 12+ months inactive
        yield 'Inactive no courses' => [
            'nombreCours' => 0,
            'lastVisit' => new \DateTimeImmutable('-13 months'),
            'shouldValidate' => true,
        ];

        // Should NOT validate: has active courses
        yield 'Has active courses' => [
            'nombreCours' => 1,
            'lastVisit' => new \DateTimeImmutable('-13 months'),
            'shouldValidate' => false,
        ];

        // Should NOT validate: recently active
        yield 'Recently active' => [
            'nombreCours' => 0,
            'lastVisit' => new \DateTimeImmutable('-2 months'),
            'shouldValidate' => false,
        ];

        // Should validate: no courses and very old
        yield 'Very old and no courses' => [
            'nombreCours' => 0,
            'lastVisit' => new \DateTimeImmutable('-24 months'),
            'shouldValidate' => true,
        ];
    }

    /**
     * Helper to create a test User entity.
     */
    private function createTestUser(
        int $nombreCours = 0,
        \DateTimeImmutable $lastVisit = new \DateTimeImmutable(),
    ): User {
        $user = new User();
        $user->email = 'test@example.com';
        $user->setPassword('hashed_password');
        $user->setPrenom('Test');
        $user->setNom('User');
        $user->setNombreCours($nombreCours);
        $user->setLastVisit($lastVisit);

        return $user;
    }
}
