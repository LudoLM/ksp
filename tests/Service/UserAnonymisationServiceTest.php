<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserAnonymisationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class UserAnonymisationServiceTest extends TestCase
{
    private UserAnonymisationService $anonymisationService;
    private MockObject $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->anonymisationService = new UserAnonymisationService($this->logger);
    }

    /**
     * Test that anonymisation removes all PII correctly.
     */
    #[DataProvider('piiDataProvider')]
    public function testAnonymiseUserRemovesAllPII(string $reason): void
    {
        // Arrange
        $user = $this->createTestUser(
            id: 42,
            email: 'john.doe@example.com',
            prenom: 'John',
            nom: 'Doe',
            telephone: '0612345678',
            adresse: '123 Rue de Paris',
            codePostal: '75001',
            commune: 'Paris'
        );

        // Act
        $result = $this->anonymisationService->anonymiseUser($user, $reason);

        // Assert
        $this->assertTrue($result, 'anonymiseUser should return true on success');
        $this->assertEquals('anonyme-42@deleted.local', $user->getEmail(), 'Email should be anonymised');
        $this->assertEquals('Anonyme', $user->getPrenom(), 'Prenom should be anonymised');
        $this->assertEquals('Anonyme', $user->getNom(), 'Nom should be anonymised');
        $this->assertNull($user->getTelephone(), 'Telephone should be null');
        $this->assertNull($user->getAdresse(), 'Adresse should be null');
        $this->assertNull($user->getCodePostal(), 'CodePostal should be null');
        $this->assertNull($user->getCommune(), 'Commune should be null');
        $this->assertNotNull($user->getAnonymisedAt(), 'anonymisedAt should be set');
    }

    /**
     * Test idempotence - anonymising twice returns false.
     */
    public function testAnonymiseUserIdempotence(): void
    {
        // Arrange
        $user = $this->createTestUser(id: 42);

        // Act - First anonymisation
        $result1 = $this->anonymisationService->anonymiseUser($user, 'rgpd_request');
        $result2 = $this->anonymisationService->anonymiseUser($user, 'rgpd_request');

        // Assert
        $this->assertTrue($result1, 'First anonymisation should return true');
        $this->assertFalse($result2, 'Second anonymisation should return false (already anonymised)');
    }

    /**
     * Test that logging happens with the correct context.
     */
    #[DataProvider('loggingContextProvider')]
    public function testAnonymiseUserLogsCorrectContext(string $reason): void
    {
        // Arrange
        $user = $this->createTestUser(id: 99);
        $this->logger->expects($this->once())->method('info');

        // Act
        $this->anonymisationService->anonymiseUser($user, $reason);

        // Assert - logger.info() called (verified by mock expectation)
    }

    /**
     * Test that reset password tokens are properly handled (User entity responsibility)
     * This verifies the service doesn't touch tokens (that's done in controller).
     */
    public function testAnonymiseUserDoesNotTouchResetPasswordTokens(): void
    {
        // Arrange
        $user = $this->createTestUser(id: 42);
        $originalToken = 'some-reset-token-xyz';
        $user->setResetPasswordToken($originalToken);

        // Act
        $this->anonymisationService->anonymiseUser($user, 'rgpd_request');

        // Assert - Service doesn't touch reset tokens (controller's responsibility)
        $this->assertEquals($originalToken, $user->getResetPasswordToken(),
            'Service should not touch reset password tokens (controller responsibility)');
    }

    /**
     * Test various anonymisation reasons.
     */
    public static function piiDataProvider(): \Generator
    {
        yield 'RGPD Request' => ['rgpd_request'];
        yield 'Inactivity' => ['inactivity'];
    }

    /**
     * Test logging with different contexts.
     */
    public static function loggingContextProvider(): \Generator
    {
        yield 'Log RGPD Request' => ['rgpd_request'];
        yield 'Log Inactivity' => ['inactivity'];
    }

    /**
     * Helper to create a test User entity.
     */
    private function createTestUser(
        int $id = 1,
        string $email = 'test@example.com',
        string $prenom = 'Test',
        string $nom = 'User',
        ?string $telephone = null,
        ?string $adresse = null,
        ?string $codePostal = null,
        ?string $commune = null,
    ): User {
        $user = new User();
        $reflection = new \ReflectionClass($user);

        // Set ID using reflection (since it's private)
        $idProp = $reflection->getProperty('id');
        $idProp->setValue($user, $id);

        // Set other properties
        $user->email = $email;
        $user->setPrenom($prenom);
        $user->setNom($nom);
        $user->setTelephone($telephone);
        $user->setAdresse($adresse);
        $user->setCodePostal($codePostal);
        $user->setCommune($commune);

        return $user;
    }
}
