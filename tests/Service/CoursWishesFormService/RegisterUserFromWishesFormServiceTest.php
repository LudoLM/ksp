<?php

declare(strict_types=1);

namespace App\Tests\Service\CoursWishesFormService;

use App\DTO\CreateUserDTO;
use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\User;
use App\Exception\UnvalidatedWishesFormException;
use App\Service\CoursWishesFormService\RegisterUserFromWishesFormService;
use App\Service\CoursWishesFormService\ResolveWishesFormByTokenService;
use App\Service\UserControllerService\CreateOrEditUserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegisterUserFromWishesFormService::class)]
class RegisterUserFromWishesFormServiceTest extends TestCase
{
    private ResolveWishesFormByTokenService&MockObject $resolveWishesFormByTokenService;
    private CreateOrEditUserService&MockObject $createOrEditUserService;
    private EntityManagerInterface&MockObject $em;
    private RegisterUserFromWishesFormService $service;

    protected function setUp(): void
    {
        $this->resolveWishesFormByTokenService = $this->createMock(ResolveWishesFormByTokenService::class);
        $this->createOrEditUserService = $this->createMock(CreateOrEditUserService::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->service = new RegisterUserFromWishesFormService(
            $this->resolveWishesFormByTokenService,
            $this->createOrEditUserService,
            $this->em,
        );
    }

    private function createForm(): CoursWishesForm
    {
        $form = new CoursWishesForm();
        $form->setEmail('jean@example.com');
        $form->setNom('Dupont');
        $form->setPrenom('Jean');
        $form->setTelephone('0612345678');
        $form->setSaison('2026-2027');
        $form->setPackSouhaite(new Pack());
        $form->setModeReglement('CbComptant');
        $form->setStatus('Valide');
        $form->setSubmittedAt(new \DateTimeImmutable());
        $form->setRegistrationTokenHash('hash');
        $form->setRegistrationTokenExpiresAt(new \DateTimeImmutable('+1 day'));

        return $form;
    }

    public function testRegisterHydratesDtoFromFormAndLinksUser(): void
    {
        $form = $this->createForm();
        $dto = new CreateUserDTO();
        $dto->token = 'raw-token';
        $user = new User();

        $this->resolveWishesFormByTokenService->method('resolveByRegistrationToken')->with('raw-token')->willReturn($form);
        $this->createOrEditUserService->expects($this->once())
            ->method('createOrEditUser')
            ->with(null, $this->callback(fn (CreateUserDTO $dto): bool => 'jean@example.com' === $dto->email
                && 'Dupont' === $dto->nom
                && 'Jean' === $dto->prenom
                && '0612345678' === $dto->telephone))
            ->willReturn($user);
        $this->em->expects($this->once())->method('persist')->with($user);
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->register($dto);

        $this->assertSame($user, $result);
        $this->assertSame($user, $form->getUser());
        $this->assertNull($form->getRegistrationTokenHash());
        $this->assertNull($form->getRegistrationTokenExpiresAt());
    }

    public function testRegisterThrowsWhenNoTokenIsProvided(): void
    {
        $dto = new CreateUserDTO();
        $dto->token = null;

        $this->resolveWishesFormByTokenService->expects($this->never())->method('resolveByRegistrationToken');
        $this->createOrEditUserService->expects($this->never())->method('createOrEditUser');
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $this->expectException(UnvalidatedWishesFormException::class);

        $this->service->register($dto);
    }

    public function testRegisterThrowsWhenTheTokenDoesNotResolve(): void
    {
        $dto = new CreateUserDTO();
        $dto->token = 'unknown-token';

        $this->resolveWishesFormByTokenService->method('resolveByRegistrationToken')->willReturn(null);
        $this->createOrEditUserService->expects($this->never())->method('createOrEditUser');
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $this->expectException(UnvalidatedWishesFormException::class);

        $this->service->register($dto);
    }

    public function testRegisterPropagatesCreateOrEditUserValidationFailure(): void
    {
        $form = $this->createForm();
        $dto = new CreateUserDTO();
        $dto->token = 'raw-token';

        $this->resolveWishesFormByTokenService->method('resolveByRegistrationToken')->willReturn($form);
        $this->createOrEditUserService->method('createOrEditUser')
            ->willThrowException(new \InvalidArgumentException('{"errors":[]}'));
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->never())->method('flush');

        $this->expectException(\InvalidArgumentException::class);

        $this->service->register($dto);
    }
}
