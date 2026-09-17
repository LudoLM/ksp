<?php

declare(strict_types=1);

namespace App\Tests\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\User;
use App\Repository\CoursWishesFormRepository;
use App\Service\CoursWishesFormService\ResolveWishesFormByTokenService;
use App\Service\Security\SecureTokenService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResolveWishesFormByTokenService::class)]
class ResolveWishesFormByTokenServiceTest extends TestCase
{
    private CoursWishesFormRepository&MockObject $repository;
    private SecureTokenService&MockObject $secureTokenService;
    private ResolveWishesFormByTokenService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CoursWishesFormRepository::class);
        $this->secureTokenService = $this->createMock(SecureTokenService::class);
        $this->service = new ResolveWishesFormByTokenService($this->repository, $this->secureTokenService);
    }

    private function createForm(?\DateTimeImmutable $correctionExpiresAt = null, ?\DateTimeImmutable $registrationExpiresAt = null): CoursWishesForm
    {
        $form = new CoursWishesForm();
        $form->setEmail('jean@example.com');
        $form->setSaison('2026-2027');
        $form->setPackSouhaite(new Pack());
        $form->setModeReglement('CbComptant');
        $form->setStatus('ACorriger');
        $form->setSubmittedAt(new \DateTimeImmutable());
        $form->setCorrectionTokenHash('hashed-value');
        $form->setCorrectionTokenExpiresAt($correctionExpiresAt);
        $form->setRegistrationTokenHash('hashed-value');
        $form->setRegistrationTokenExpiresAt($registrationExpiresAt);

        return $form;
    }

    // --- resolveByCorrectionToken ---

    public function testResolveByCorrectionTokenReturnsTheFormForAValidUnexpiredToken(): void
    {
        $form = $this->createForm(correctionExpiresAt: new \DateTimeImmutable('+1 day'));

        $this->secureTokenService->method('hash')->with('raw-token')->willReturn('hashed-value');
        $this->repository->method('findOneBy')
            ->with(['correctionTokenHash' => 'hashed-value'])
            ->willReturn($form);

        $this->assertSame($form, $this->service->resolveByCorrectionToken('raw-token'));
    }

    public function testResolveByCorrectionTokenReturnsTheFormEvenWhenLinkedToAUser(): void
    {
        $form = $this->createForm(correctionExpiresAt: new \DateTimeImmutable('+1 day'));
        $form->setUser(new User());

        $this->secureTokenService->method('hash')->willReturn('hashed-value');
        $this->repository->method('findOneBy')->willReturn($form);

        $this->assertSame($form, $this->service->resolveByCorrectionToken('raw-token'));
    }

    public function testResolveByCorrectionTokenReturnsNullWhenNoFormMatchesTheHash(): void
    {
        $this->secureTokenService->method('hash')->willReturn('hashed-value');
        $this->repository->method('findOneBy')->willReturn(null);

        $this->assertNull($this->service->resolveByCorrectionToken('unknown-token'));
    }

    public function testResolveByCorrectionTokenReturnsNullWhenTheTokenHasExpired(): void
    {
        $form = $this->createForm(correctionExpiresAt: new \DateTimeImmutable('-1 minute'));

        $this->secureTokenService->method('hash')->willReturn('hashed-value');
        $this->repository->method('findOneBy')->willReturn($form);

        $this->assertNull($this->service->resolveByCorrectionToken('raw-token'));
    }

    public function testResolveByCorrectionTokenReturnsNullWhenNoExpirationIsSet(): void
    {
        $form = $this->createForm();

        $this->secureTokenService->method('hash')->willReturn('hashed-value');
        $this->repository->method('findOneBy')->willReturn($form);

        $this->assertNull($this->service->resolveByCorrectionToken('raw-token'));
    }

    // --- resolveByRegistrationToken ---

    public function testResolveByRegistrationTokenReturnsTheFormForAValidUnexpiredToken(): void
    {
        $form = $this->createForm(registrationExpiresAt: new \DateTimeImmutable('+1 day'));

        $this->secureTokenService->method('hash')->with('raw-token')->willReturn('hashed-value');
        $this->repository->method('findOneBy')
            ->with(['registrationTokenHash' => 'hashed-value'])
            ->willReturn($form);

        $this->assertSame($form, $this->service->resolveByRegistrationToken('raw-token'));
    }

    public function testResolveByRegistrationTokenReturnsNullWhenNoFormMatchesTheHash(): void
    {
        $this->secureTokenService->method('hash')->willReturn('hashed-value');
        $this->repository->method('findOneBy')->willReturn(null);

        $this->assertNull($this->service->resolveByRegistrationToken('unknown-token'));
    }

    public function testResolveByRegistrationTokenReturnsNullWhenTheTokenHasExpired(): void
    {
        $form = $this->createForm(registrationExpiresAt: new \DateTimeImmutable('-1 minute'));

        $this->secureTokenService->method('hash')->willReturn('hashed-value');
        $this->repository->method('findOneBy')->willReturn($form);

        $this->assertNull($this->service->resolveByRegistrationToken('raw-token'));
    }

    public function testResolveByRegistrationTokenReturnsNullWhenNoExpirationIsSet(): void
    {
        $form = $this->createForm();

        $this->secureTokenService->method('hash')->willReturn('hashed-value');
        $this->repository->method('findOneBy')->willReturn($form);

        $this->assertNull($this->service->resolveByRegistrationToken('raw-token'));
    }

    public function testResolveByRegistrationTokenReturnsNullWhenTheFormIsAlreadyLinkedToAUser(): void
    {
        $form = $this->createForm(registrationExpiresAt: new \DateTimeImmutable('+1 day'));
        $form->setUser(new User());

        $this->secureTokenService->method('hash')->willReturn('hashed-value');
        $this->repository->method('findOneBy')->willReturn($form);

        $this->assertNull($this->service->resolveByRegistrationToken('raw-token'));
    }
}
