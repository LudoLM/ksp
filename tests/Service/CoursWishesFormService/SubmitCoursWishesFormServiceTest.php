<?php

declare(strict_types=1);

namespace App\Tests\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\SeasonPlanningSlot;
use App\Entity\User;
use App\Enum\PaymentMethodEnum;
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
            nom: null,
            prenom: null,
            telephone: null,
            creneauPrimaire: null,
            creneauSecondaire: null,
            packSouhaite: $pack,
            modeReglement: PaymentMethodEnum::CARD_ONE_TIME->value,
        );

        $this->assertSame('jean@example.com', $form->getEmail());
        $this->assertSame(SaisonHelper::current(), $form->getSaison());
        $this->assertSame(StatusCoursWishesFormEnum::EN_ATTENTE->value, $form->getStatus());
        $this->assertFalse($form->isFilledByAdmin());
    }

    public function testSubmitStoresNomPrenomTelephoneForAnAnonymousSubmission(): void
    {
        $pack = new Pack();
        $this->repository->method('findCurrentForUser')->willReturn(null);
        $this->repository->method('findCurrentForEmail')->willReturn(null);

        $form = $this->service->submit(
            email: 'jean@example.com',
            user: null,
            nom: 'Dupont',
            prenom: 'Jean',
            telephone: '0612345678',
            creneauPrimaire: null,
            creneauSecondaire: null,
            packSouhaite: $pack,
            modeReglement: PaymentMethodEnum::CARD_ONE_TIME->value,
        );

        $this->assertSame('Dupont', $form->getNom());
        $this->assertSame('Jean', $form->getPrenom());
        $this->assertSame('0612345678', $form->getTelephone());
    }

    public function testSubmitIgnoresNomPrenomTelephoneWhenAUserIsAlreadyLinked(): void
    {
        $user = new User();
        $pack = new Pack();
        $this->repository->method('findCurrentForUser')->willReturn(null);

        $form = $this->service->submit(
            email: 'jean@example.com',
            user: $user,
            nom: 'Dupont',
            prenom: 'Jean',
            telephone: '0612345678',
            creneauPrimaire: null,
            creneauSecondaire: null,
            packSouhaite: $pack,
            modeReglement: PaymentMethodEnum::CARD_ONE_TIME->value,
        );

        $this->assertNull($form->getNom());
        $this->assertNull($form->getPrenom());
        $this->assertNull($form->getTelephone());
    }

    public function testSubmitUpdatesTheExistingFormForTheSameUserAndSaison(): void
    {
        $user = new User();
        $existing = new CoursWishesForm();
        $existing->setEmail('old@example.com');
        $existing->setStatus(StatusCoursWishesFormEnum::VALIDE->value);
        $existing->setReviewedAt(new \DateTimeImmutable());
        $staleSubmittedAt = new \DateTimeImmutable('-90 days');
        $existing->setSubmittedAt($staleSubmittedAt);

        $this->repository->method('findCurrentForUser')->with($user, SaisonHelper::current())->willReturn($existing);
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $newPrimaire = new SeasonPlanningSlot();
        $pack = new Pack();

        $form = $this->service->submit(
            email: 'jean@example.com',
            user: $user,
            nom: null,
            prenom: null,
            telephone: null,
            creneauPrimaire: $newPrimaire,
            creneauSecondaire: null,
            packSouhaite: $pack,
            modeReglement: PaymentMethodEnum::CHECK_3X->value,
        );

        $this->assertSame($existing, $form);
        $this->assertSame($newPrimaire, $form->getCreneauPrimaire());
        $this->assertSame(StatusCoursWishesFormEnum::EN_ATTENTE->value, $form->getStatus());
        $this->assertNull($form->getReviewedAt());
        $this->assertGreaterThan($staleSubmittedAt, $form->getSubmittedAt());
    }

    public function testSubmitMarksTheFormAsFilledByAdminWhenRequested(): void
    {
        $this->repository->method('findCurrentForUser')->willReturn(null);
        $this->repository->method('findCurrentForEmail')->willReturn(null);

        $form = $this->service->submit(
            email: 'jean@example.com',
            user: new User(),
            nom: null,
            prenom: null,
            telephone: null,
            creneauPrimaire: null,
            creneauSecondaire: null,
            packSouhaite: new Pack(),
            modeReglement: PaymentMethodEnum::CARD_ONE_TIME->value,
            filledByAdmin: true,
        );

        $this->assertTrue($form->isFilledByAdmin());
    }

    public function testAnonymousResubmissionReusesTheExistingUnlinkedFormForTheSameEmail(): void
    {
        $existing = new CoursWishesForm();
        $existing->setEmail('jean@example.com');
        $existing->setStatus(StatusCoursWishesFormEnum::A_CORRIGER->value);
        $existing->setCorrectionReason('Créneau complet');
        // Simule un dossier ayant déjà été approuvé une fois (token émis et
        // envoyé), puis renvoyé en correction sans que le lien ait expiré :
        // la resoumission doit invalider ce token, pas seulement le statut.
        $existing->setRegistrationTokenHash('old-token-hash');
        $existing->setRegistrationTokenExpiresAt(new \DateTimeImmutable('+30 days'));
        // Simule le token de correction envoyé pour ce même passage en correction :
        // la resoumission doit aussi l'invalider, sinon l'ancien lien de correction
        // resterait exploitable après coup.
        $existing->setCorrectionTokenHash('old-correction-token-hash');
        $existing->setCorrectionTokenExpiresAt(new \DateTimeImmutable('+30 days'));

        $this->repository->method('findCurrentForEmail')->with('jean@example.com', SaisonHelper::current())->willReturn($existing);
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $form = $this->service->submit(
            email: 'jean@example.com',
            user: null,
            nom: null,
            prenom: null,
            telephone: null,
            creneauPrimaire: null,
            creneauSecondaire: null,
            packSouhaite: new Pack(),
            modeReglement: PaymentMethodEnum::CARD_ONE_TIME->value,
        );

        $this->assertSame($existing, $form);
        $this->assertSame(StatusCoursWishesFormEnum::EN_ATTENTE->value, $form->getStatus());
        $this->assertNull($form->getCorrectionReason());
        $this->assertNull($form->getRegistrationTokenHash());
        $this->assertNull($form->getRegistrationTokenExpiresAt());
        $this->assertNull($form->getCorrectionTokenHash());
        $this->assertNull($form->getCorrectionTokenExpiresAt());
    }

    public function testSubmitWithATargetFormWritesDirectlyToItWithoutLookingItUpBySessionOrEmail(): void
    {
        $existingUser = new User();
        $targetForm = new CoursWishesForm();
        $targetForm->setEmail('marie@example.com');
        $targetForm->setStatus(StatusCoursWishesFormEnum::A_CORRIGER->value);
        $targetForm->setUser($existingUser);

        $this->repository->expects($this->never())->method('findCurrentForUser');
        $this->repository->expects($this->never())->method('findCurrentForEmail');
        $this->em->expects($this->never())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $newPrimaire = new SeasonPlanningSlot();

        $form = $this->service->submit(
            email: 'marie@example.com',
            user: $existingUser,
            nom: null,
            prenom: null,
            telephone: null,
            creneauPrimaire: $newPrimaire,
            creneauSecondaire: null,
            packSouhaite: new Pack(),
            modeReglement: PaymentMethodEnum::CARD_ONE_TIME->value,
            targetForm: $targetForm,
        );

        $this->assertSame($targetForm, $form);
        $this->assertSame($newPrimaire, $form->getCreneauPrimaire());
        $this->assertSame($existingUser, $form->getUser());
        $this->assertSame(StatusCoursWishesFormEnum::EN_ATTENTE->value, $form->getStatus());
    }

    public function testSubmitWithATargetFormIgnoresAnEmailSentByTheClientAndKeepsTheExistingOne(): void
    {
        $targetForm = new CoursWishesForm();
        $targetForm->setEmail('original@example.com');
        $targetForm->setStatus(StatusCoursWishesFormEnum::A_CORRIGER->value);

        $form = $this->service->submit(
            email: 'attacker-supplied@example.com',
            user: null,
            nom: 'Dupont',
            prenom: 'Jean',
            telephone: '0612345678',
            creneauPrimaire: null,
            creneauSecondaire: null,
            packSouhaite: new Pack(),
            modeReglement: PaymentMethodEnum::CARD_ONE_TIME->value,
            targetForm: $targetForm,
        );

        $this->assertSame('original@example.com', $form->getEmail());
    }

    public function testAdminSubmitReusesAnUnlinkedAnonymousFormMatchingTheUsersEmailAndLinksIt(): void
    {
        $user = new User();
        $existing = new CoursWishesForm();
        $existing->setEmail('jean@example.com');
        $existing->setStatus(StatusCoursWishesFormEnum::EN_ATTENTE->value);

        $this->repository->method('findCurrentForUser')->with($user, SaisonHelper::current())->willReturn(null);
        $this->repository->method('findCurrentForEmail')->with('jean@example.com', SaisonHelper::current())->willReturn($existing);
        $this->em->expects($this->never())->method('persist');

        $form = $this->service->submit(
            email: 'jean@example.com',
            user: $user,
            nom: null,
            prenom: null,
            telephone: null,
            creneauPrimaire: null,
            creneauSecondaire: null,
            packSouhaite: new Pack(),
            modeReglement: PaymentMethodEnum::CARD_ONE_TIME->value,
            filledByAdmin: true,
        );

        $this->assertSame($existing, $form);
        $this->assertSame($user, $form->getUser());
    }
}
