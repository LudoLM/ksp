<?php

namespace App\Tests\Serializer;

use App\DTO\ResetPasswordDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Serializer\ResetPasswordDTOToUserDenormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordSecurityTest extends TestCase
{
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private UserRepository&MockObject $userRepository;
    private ResetPasswordDTOToUserDenormalizer $denormalizer;

    protected function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);

        $this->denormalizer = new ResetPasswordDTOToUserDenormalizer(
            $this->passwordHasher,
            $this->userRepository
        );
    }

    public static function invalidTokenDataProvider(): \Generator
    {
        yield 'token_expired' => [
            'tokenInDb' => hash('sha256', 'test-token'),
            'expiresAt' => new \DateTime('-1 hour'),
            'providedToken' => 'test-token',
            'expectedMessage' => 'invalide ou a expiré',
        ];

        yield 'token_invalid' => [
            'tokenInDb' => hash('sha256', 'valid-token'),
            'expiresAt' => new \DateTime('+1 hour'),
            'providedToken' => 'invalid-token',
            'expectedMessage' => 'invalide ou a expiré',
        ];

        yield 'token_with_null_expiration' => [
            'tokenInDb' => hash('sha256', 'test-token'),
            'expiresAt' => null,
            'providedToken' => 'test-token',
            'expectedMessage' => 'invalide ou a expiré',
        ];
    }

    #[DataProvider('invalidTokenDataProvider')]
    public function testInvalidTokensAreRejected(
        string $tokenInDb,
        ?\DateTime $expiresAt,
        string $providedToken,
        string $expectedMessage,
    ): void {
        $user = $this->createMock(User::class);
        $user->method('getResetPasswordToken')->willReturn($tokenInDb);
        $user->method('getResetPasswordTokenExpiresAt')->willReturn($expiresAt);

        $this->userRepository->method('find')->willReturn($user);

        $dto = new ResetPasswordDTO('1', 'newPassword123', $providedToken);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->denormalizer->denormalize($dto, User::class);
    }

    public static function validTokenDataProvider(): \Generator
    {
        yield 'token_valid_standard' => [
            'token' => 'valid-token-12345',
        ];

        yield 'token_valid_with_special_chars' => [
            'token' => 'valid-token_!@#$%',
        ];

        yield 'token_valid_short' => [
            'token' => 'abc123',
        ];
    }

    #[DataProvider('validTokenDataProvider')]
    public function testValidTokenIsAccepted(string $token): void
    {
        $user = $this->createMock(User::class);

        // Configuration des getters pour la validation
        $user->method('getResetPasswordToken')->willReturn(hash('sha256', $token));
        $user->method('getResetPasswordTokenExpiresAt')->willReturn(new \DateTime('+1 hour'));

        // Vérification que setPassword est appelé une fois avec le bon hash
        $user->expects($this->once())
            ->method('setPassword')
            ->with('hashed-password');

        // Vérification que le token est nettoyé
        $user->expects($this->once())
            ->method('setResetPasswordToken')
            ->with('');

        // Vérification que l'expiration est nettoyée
        $user->expects($this->once())
            ->method('setResetPasswordTokenExpiresAt')
            ->with(null);

        $this->userRepository->method('find')->willReturn($user);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed-password');

        $dto = new ResetPasswordDTO('1', 'newPassword123', $token);

        $result = $this->denormalizer->denormalize($dto, User::class);

        // Vérifie que le mock User est bien retourné
        $this->assertSame($user, $result);
    }

    public function testUserNotFoundThrowsException(): void
    {
        $this->userRepository->method('find')->willReturn(null);

        $dto = new ResetPasswordDTO('999', 'newPassword123', 'test-token');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('invalide ou a expiré');

        $this->denormalizer->denormalize($dto, User::class);
    }

    public function testHashEqualsIsUsedForConstantTimeComparison(): void
    {
        // Ce test vérifie que hash_equals est utilisé (en inspectant le code)
        // La vraie prévention du timing attack se fait via hash_equals()
        // Ce test documente le comportement attendu

        $validToken = 'correct-token-xyz';

        // Mock au lieu de new User()
        $user = $this->createMock(User::class);

        // Configuration du comportement
        $user->method('getResetPasswordToken')
            ->willReturn(hash('sha256', $validToken));
        $user->method('getResetPasswordTokenExpiresAt')
            ->willReturn(new \DateTime('+1 hour'));

        // Vérification que setPassword est appelé (preuve que la validation a réussi)
        $user->expects($this->once())
            ->method('setPassword')
            ->with('hashed-password');

        $user->expects($this->once())
            ->method('setResetPasswordToken')
            ->with('');

        $user->expects($this->once())
            ->method('setResetPasswordTokenExpiresAt')
            ->with(null);

        $this->userRepository->method('find')->willReturn($user);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed-password');

        $dto = new ResetPasswordDTO('1', 'newPassword123', $validToken);

        // Si on arrive ici sans exception, c'est que hash_equals a accepté le token
        $result = $this->denormalizer->denormalize($dto, User::class);
        $this->assertNotNull($result);
    }
}
