<?php

declare(strict_types=1);

namespace App\Tests\Service\CoursWishesFormService;

use App\DTO\SubmitCoursWishesFormDTO;
use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\SeasonPlanningSlot;
use App\Entity\User;
use App\Exception\InvalidCoursWishesFormSubmissionException;
use App\Repository\PackRepository;
use App\Repository\SeasonPlanningSlotRepository;
use App\Service\CoursWishesFormService\CheckExistingAccountService;
use App\Service\CoursWishesFormService\SubmitCoursWishesFormRequestService;
use App\Service\CoursWishesFormService\SubmitCoursWishesFormService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SubmitCoursWishesFormRequestService::class)]
class SubmitCoursWishesFormRequestServiceTest extends TestCase
{
    private PackRepository&MockObject $packRepository;
    private SeasonPlanningSlotRepository&MockObject $seasonPlanningSlotRepository;
    private CheckExistingAccountService&MockObject $checkExistingAccountService;
    private SubmitCoursWishesFormService&MockObject $submitService;
    private SubmitCoursWishesFormRequestService $service;

    protected function setUp(): void
    {
        $this->packRepository = $this->createMock(PackRepository::class);
        $this->seasonPlanningSlotRepository = $this->createMock(SeasonPlanningSlotRepository::class);
        $this->checkExistingAccountService = $this->createMock(CheckExistingAccountService::class);
        $this->submitService = $this->createMock(SubmitCoursWishesFormService::class);
        $this->service = new SubmitCoursWishesFormRequestService(
            $this->packRepository,
            $this->seasonPlanningSlotRepository,
            $this->checkExistingAccountService,
            $this->submitService,
        );
    }

    private function createDto(): SubmitCoursWishesFormDTO
    {
        $dto = new SubmitCoursWishesFormDTO();
        $dto->email = 'jean@example.com';
        $dto->nom = 'Dupont';
        $dto->prenom = 'Jean';
        $dto->telephone = '0612345678';
        $dto->creneauPrimaireId = 1;
        $dto->creneauSecondaireId = null;
        $dto->packSouhaiteId = 10;
        $dto->modeReglement = 'CbComptant';

        return $dto;
    }

    public function testHandleThrowsWhenPackNotFound(): void
    {
        $this->packRepository->method('find')->with(10)->willReturn(null);
        $this->submitService->expects($this->never())->method('submit');

        $this->expectException(InvalidCoursWishesFormSubmissionException::class);
        $this->expectExceptionMessage('Pack introuvable');

        $this->service->handle($this->createDto(), null, false);
    }

    public function testHandleThrowsWhenNomMissingForAnonymousSubmission(): void
    {
        $this->packRepository->method('find')->willReturn(new Pack());
        $dto = $this->createDto();
        $dto->nom = '';

        $this->expectException(InvalidCoursWishesFormSubmissionException::class);
        $this->expectExceptionMessage('Le nom est requis.');

        $this->service->handle($dto, null, false);
    }

    public function testHandleThrowsWhenPrenomMissingForAnonymousSubmission(): void
    {
        $this->packRepository->method('find')->willReturn(new Pack());
        $dto = $this->createDto();
        $dto->prenom = null;

        $this->expectException(InvalidCoursWishesFormSubmissionException::class);
        $this->expectExceptionMessage('Le prénom est requis.');

        $this->service->handle($dto, null, false);
    }

    public function testHandleThrowsWhenTelephoneMissingForAnonymousSubmission(): void
    {
        $this->packRepository->method('find')->willReturn(new Pack());
        $dto = $this->createDto();
        $dto->telephone = '   ';

        $this->expectException(InvalidCoursWishesFormSubmissionException::class);
        $this->expectExceptionMessage('Le téléphone est requis.');

        $this->service->handle($dto, null, false);
    }

    public function testHandleDoesNotValidateAnonymousFieldsWhenAUserIsLoggedIn(): void
    {
        $this->packRepository->method('find')->willReturn(new Pack());
        $this->seasonPlanningSlotRepository->method('find')->willReturn(new SeasonPlanningSlot());
        $this->checkExistingAccountService->expects($this->never())->method('handleIfExists');
        $this->submitService->method('submit')->willReturn(new CoursWishesForm());

        $dto = $this->createDto();
        $dto->nom = null;
        $dto->prenom = null;
        $dto->telephone = null;

        $result = $this->service->handle($dto, new User(), false);

        $this->assertFalse($result->accountAlreadyExists);
    }

    public function testHandleShortCircuitsWhenAnAccountAlreadyExistsForTheEmail(): void
    {
        $this->packRepository->method('find')->willReturn(new Pack());
        $this->seasonPlanningSlotRepository->method('find')->willReturn(new SeasonPlanningSlot());
        $this->checkExistingAccountService->method('handleIfExists')->with('jean@example.com')->willReturn(true);
        $this->submitService->expects($this->never())->method('submit');

        $result = $this->service->handle($this->createDto(), null, false);

        $this->assertTrue($result->accountAlreadyExists);
    }

    public function testHandleThrowsWhenCreneauPrimaireNotFound(): void
    {
        $this->packRepository->method('find')->willReturn(new Pack());
        $this->seasonPlanningSlotRepository->method('find')->willReturn(null);
        $this->checkExistingAccountService->expects($this->never())->method('handleIfExists');
        $this->submitService->expects($this->never())->method('submit');

        $this->expectException(InvalidCoursWishesFormSubmissionException::class);
        $this->expectExceptionMessage('Créneau prioritaire introuvable');

        $this->service->handle($this->createDto(), null, false);
    }

    public function testHandleSkipsTheExistingAccountCheckAndForwardsTheTargetFormWhenGiven(): void
    {
        $pack = new Pack();
        $creneauPrimaire = new SeasonPlanningSlot();
        $targetForm = new CoursWishesForm();
        $targetForm->setEmail('jean@example.com');

        $this->packRepository->method('find')->with(10)->willReturn($pack);
        $this->seasonPlanningSlotRepository->method('find')->willReturn($creneauPrimaire);
        $this->checkExistingAccountService->expects($this->never())->method('handleIfExists');
        $this->submitService->expects($this->once())
            ->method('submit')
            ->with(
                email: 'jean@example.com',
                user: null,
                nom: 'Dupont',
                prenom: 'Jean',
                telephone: '0612345678',
                creneauPrimaire: $creneauPrimaire,
                creneauSecondaire: null,
                packSouhaite: $pack,
                modeReglement: 'CbComptant',
                filledByAdmin: false,
                targetForm: $targetForm,
            )
            ->willReturn($targetForm);

        $result = $this->service->handle($this->createDto(), null, false, $targetForm);

        $this->assertSame($targetForm, $result->form());
    }

    public function testHandleReturnsTheSubmittedFormOnSuccess(): void
    {
        $pack = new Pack();
        $creneauPrimaire = new SeasonPlanningSlot();
        $creneauSecondaire = new SeasonPlanningSlot();
        $form = new CoursWishesForm();

        $this->packRepository->method('find')->with(10)->willReturn($pack);
        $this->checkExistingAccountService->method('handleIfExists')->willReturn(false);
        $this->seasonPlanningSlotRepository->method('find')
            ->willReturnMap([
                [1, $creneauPrimaire],
                [2, $creneauSecondaire],
            ]);
        $this->submitService->expects($this->once())
            ->method('submit')
            ->with(
                email: 'jean@example.com',
                user: null,
                nom: 'Dupont',
                prenom: 'Jean',
                telephone: '0612345678',
                creneauPrimaire: $creneauPrimaire,
                creneauSecondaire: $creneauSecondaire,
                packSouhaite: $pack,
                modeReglement: 'CbComptant',
                filledByAdmin: false,
                targetForm: null,
            )
            ->willReturn($form);

        $dto = $this->createDto();
        $dto->creneauSecondaireId = 2;

        $result = $this->service->handle($dto, null, false);

        $this->assertFalse($result->accountAlreadyExists);
        $this->assertSame($form, $result->form());
    }
}
