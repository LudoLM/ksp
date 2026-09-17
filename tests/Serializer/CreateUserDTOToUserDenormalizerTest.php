<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use App\DTO\CreateUserDTO;
use App\Entity\User;
use App\Serializer\CreateUserDTOToUserDenormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[CoversClass(CreateUserDTOToUserDenormalizer::class)]
class CreateUserDTOToUserDenormalizerTest extends TestCase
{
    private UserPasswordHasherInterface&MockObject $hasher;
    private CreateUserDTOToUserDenormalizer $denormalizer;

    protected function setUp(): void
    {
        $this->hasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->hasher->method('hashPassword')->willReturn('hashed');
        $this->denormalizer = new CreateUserDTOToUserDenormalizer($this->hasher);
    }

    public function testDenormalizeSetsPrenomNomTelephoneWhenProvided(): void
    {
        $dto = new CreateUserDTO();
        $dto->email = 'jean@example.com';
        $dto->password = 'password123';
        $dto->prenom = 'Jean';
        $dto->nom = 'Dupont';
        $dto->telephone = '0612345678';

        $user = $this->denormalizer->denormalize($dto, User::class);

        $this->assertSame('Jean', $user->getPrenom());
        $this->assertSame('Dupont', $user->getNom());
        $this->assertSame('0612345678', $user->getTelephone());
    }

    public function testDenormalizeSkipsPrenomNomTelephoneWhenNull(): void
    {
        $dto = new CreateUserDTO();
        $dto->email = 'jean@example.com';
        $dto->password = 'password123';

        $user = $this->denormalizer->denormalize($dto, User::class);

        $this->assertNull($user->getNom());
    }

    public function testDenormalizeSkipsEmailWhenNull(): void
    {
        $dto = new CreateUserDTO();
        $dto->password = 'password123';
        $dto->prenom = 'Jean';
        $dto->nom = 'Dupont';
        $dto->telephone = '0612345678';

        $user = $this->denormalizer->denormalize($dto, User::class);

        $this->assertFalse(isset($user->email));
    }
}
