<?php

declare(strict_types=1);

namespace App\Tests\Service\CoursWishesFormService;

use App\Entity\CoursWeekType;
use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\User;
use App\Enum\ModeReglementEnum;
use App\Enum\StatusCoursWishesFormEnum;
use App\Helper\SaisonHelper;
use App\Repository\CoursWishesFormRepository;
use App\Service\CoursWishesFormService\SubmitCoursWishesFormService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SubmitCoursWishesFormService::class)]
class SubmitCoursWishesFormServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private CoursWishesFormRepository&MockObject $repository;
    private SubmitCoursWishesFormService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(CoursWishesFormRepository::class);
        $this->service = new SubmitCoursWishesFormService($this->em, $this->repository);
    }

    public function testSubmitCreatesANewFormWhenNoneExists(): void
    {
        $pack = new Pack();
        $this->repository->method('findCurrentForUser')->willReturn(null);
        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(CoursWishesForm::class));
        $this->em->expects($this->once())->method('flush');

        $form = $this->service->submit(
            email: 'jean@example.com',
            user: null,
            creneauPrimaire: null,
            creneauSecondaire: null,
            packSouhaite: $pack,
            modeReglement: ModeReglementEnum::CB_COMPTANT->value,
        );

        $this->assertSame('jean@example.com', $form->getEmail());
        $this->assertSame(SaisonHelper::current(), $form->getSaison());
        $this->assertSame(StatusCoursWishesFormEnum::EN_ATTENTE->value, $form->getStatus());
        $this->assertFalse($form->isFilledByAdmin());
    }

    public function testSubmitUpdatesTheExistingFormForTheSameUserAndSaison(): void
    {
        $user = new User();
        $existing = new CoursWishesForm();
        $existing->setEmail('old@example.com');
        $existing->setStatus(StatusCoursWishesFormEnum::VALIDE->value);
        $existing->setValidatedAt(new \DateTimeImmutable());

        $this->repository->method('findCurrentForUser')->with($user, SaisonHelper::current())->willReturn($existing);
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $newPrimaire = new CoursWeekType();
        $pack = new Pack();

        $form = $this->service->submit(
            email: 'jean@example.com',
            user: $user,
            creneauPrimaire: $newPrimaire,
            creneauSecondaire: null,
            packSouhaite: $pack,
            modeReglement: ModeReglementEnum::CHEQUE_3X->value,
        );

        $this->assertSame($existing, $form);
        $this->assertSame($newPrimaire, $form->getCreneauPrimaire());
        $this->assertSame(StatusCoursWishesFormEnum::EN_ATTENTE->value, $form->getStatus());
        $this->assertNull($form->getValidatedAt());
    }

    public function testSubmitMarksTheFormAsFilledByAdminWhenRequested(): void
    {
        $this->repository->method('findCurrentForUser')->willReturn(null);

        $form = $this->service->submit(
            email: 'jean@example.com',
            user: new User(),
            creneauPrimaire: null,
            creneauSecondaire: null,
            packSouhaite: new Pack(),
            modeReglement: ModeReglementEnum::CB_COMPTANT->value,
            filledByAdmin: true,
        );

        $this->assertTrue($form->isFilledByAdmin());
    }
}
