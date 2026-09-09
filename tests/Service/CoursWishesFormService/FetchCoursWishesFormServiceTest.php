<?php

declare(strict_types=1);

namespace App\Tests\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\SeasonPlanning;
use App\Entity\SeasonPlanningSlot;
use App\Entity\TypeCours;
use App\Entity\User;
use App\Enum\StatusCoursWishesFormEnum;
use App\Repository\CoursWishesFormRepository;
use App\Service\CoursWishesFormService\FetchCoursWishesFormService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(FetchCoursWishesFormService::class)]
class FetchCoursWishesFormServiceTest extends TestCase
{
    private CoursWishesFormRepository&MockObject $repository;
    private FetchCoursWishesFormService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CoursWishesFormRepository::class);
        $this->service = new FetchCoursWishesFormService($this->repository);
    }

    private function createForm(int $id, string $email, string $status): CoursWishesForm
    {
        $form = new CoursWishesForm();
        $form->setEmail($email);
        $form->setSaison('2026-2027');
        $form->setPackSouhaite(new Pack()->setNom('Pack Test'));
        $form->setModeReglement('CbComptant');
        $form->setStatus($status);
        $form->setSubmittedAt(new \DateTimeImmutable());

        $idProp = new \ReflectionClass($form)->getProperty('id');
        $idProp->setValue($form, $id);

        return $form;
    }

    private function createSlot(int $id, int $daySelected, string $time, array $coursLibelles): SeasonPlanningSlot
    {
        $slot = new SeasonPlanningSlot();
        $slot->setSeasonPlanning(new SeasonPlanning());
        $slot->setDaySelected($daySelected);
        $slot->setTimeSelected(new \DateTime($time));
        foreach ($coursLibelles as $libelle) {
            $slot->addTypeCoursOption(new TypeCours()->setLibelle($libelle));
        }

        $idProp = new \ReflectionClass($slot)->getProperty('id');
        $idProp->setValue($slot, $id);

        return $slot;
    }

    public function testGetPendingFormsResolvesCreneauPrimaireAndSecondaire(): void
    {
        $form = $this->createForm(1, 'jean@example.com', StatusCoursWishesFormEnum::EN_ATTENTE->value);
        $form->setCreneauPrimaire($this->createSlot(9, 1, '18:04', ['Pilates Début']));
        $form->setCreneauSecondaire($this->createSlot(10, 2, '19:05', ['Stretching', 'Postural Ball']));

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('count')->willReturn(1);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$form]));

        $this->repository->method('paginatePending')->with(1, 15)->willReturn($paginator);

        $result = $this->service->getPendingForms(1, 15);

        $this->assertSame([
            'id' => 9,
            'daySelected' => 1,
            'timeSelected' => '18:04',
            'cours' => 'Pilates Début',
        ], $result['data'][0]['creneauPrimaire']);
        $this->assertSame([
            'id' => 10,
            'daySelected' => 2,
            'timeSelected' => '19:05',
            'cours' => 'Stretching/Postural Ball',
        ], $result['data'][0]['creneauSecondaire']);
    }

    public function testGetPendingFormsReturnsNullCreneauxWhenNoneSelected(): void
    {
        $form = $this->createForm(1, 'jean@example.com', StatusCoursWishesFormEnum::EN_ATTENTE->value);

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('count')->willReturn(1);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$form]));

        $this->repository->method('paginatePending')->with(1, 15)->willReturn($paginator);

        $result = $this->service->getPendingForms(1, 15);

        $this->assertNull($result['data'][0]['creneauPrimaire']);
        $this->assertNull($result['data'][0]['creneauSecondaire']);
    }

    public function testGetPendingFormsExposesContactInfoPreferringTheLinkedUser(): void
    {
        $user = new User();
        $user->setNom('Martin');
        $user->setPrenom('Alice');
        $user->setTelephone('0698765432');

        $form = $this->createForm(1, 'jean@example.com', StatusCoursWishesFormEnum::EN_ATTENTE->value);
        $form->setUser($user);
        $form->setNom('Dupont');
        $form->setPrenom('Jean');
        $form->setTelephone('0612345678');

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('count')->willReturn(1);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$form]));

        $this->repository->method('paginatePending')->with(1, 15)->willReturn($paginator);

        $result = $this->service->getPendingForms(1, 15);

        $this->assertSame('Martin', $result['data'][0]['contactNom']);
        $this->assertSame('Alice', $result['data'][0]['contactPrenom']);
        $this->assertSame('0698765432', $result['data'][0]['contactTelephone']);
    }

    public function testGetPendingFormsExposesContactInfoFromTheAnonymousDossierWhenNoUserIsLinked(): void
    {
        $form = $this->createForm(1, 'jean@example.com', StatusCoursWishesFormEnum::EN_ATTENTE->value);
        $form->setNom('Dupont');
        $form->setPrenom('Jean');
        $form->setTelephone('0612345678');

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('count')->willReturn(1);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$form]));

        $this->repository->method('paginatePending')->with(1, 15)->willReturn($paginator);

        $result = $this->service->getPendingForms(1, 15);

        $this->assertSame('Dupont', $result['data'][0]['contactNom']);
        $this->assertSame('Jean', $result['data'][0]['contactPrenom']);
        $this->assertSame('0612345678', $result['data'][0]['contactTelephone']);
    }

    public function testGetPendingFormsReturnsMetadataAndData(): void
    {
        $form = $this->createForm(1, 'jean@example.com', StatusCoursWishesFormEnum::EN_ATTENTE->value);

        $paginator = $this->createMock(Paginator::class);
        $paginator->method('count')->willReturn(1);
        $paginator->method('getIterator')->willReturn(new \ArrayIterator([$form]));

        $this->repository->method('paginatePending')->with(1, 15)->willReturn($paginator);

        $result = $this->service->getPendingForms(1, 15);

        $this->assertSame(1, $result['metadata']['total_items']);
        $this->assertSame(1, $result['metadata']['current_page']);
        $this->assertSame(1, $result['metadata']['total_pages']);
        $this->assertCount(1, $result['data']);
        $this->assertSame('jean@example.com', $result['data'][0]['email']);
        $this->assertSame(StatusCoursWishesFormEnum::EN_ATTENTE->value, $result['data'][0]['status']);
    }

    public function testSelectCurrentFormPrefersThePendingOneForTheGivenSaison(): void
    {
        $validPreviousSaison = $this->createForm(1, 'jean@example.com', StatusCoursWishesFormEnum::VALIDE->value);
        $validPreviousSaison->setSaison('2025-2026');

        $pendingCurrent = $this->createForm(2, 'jean@example.com', StatusCoursWishesFormEnum::EN_ATTENTE->value);
        $pendingCurrent->setSaison('2026-2027');

        $selected = FetchCoursWishesFormService::selectCurrentForm([$validPreviousSaison, $pendingCurrent], '2026-2027');

        $this->assertSame($pendingCurrent, $selected);
    }

    public function testSelectCurrentFormReturnsNullWhenNoneMatchTheSaison(): void
    {
        $otherSaison = $this->createForm(1, 'jean@example.com', StatusCoursWishesFormEnum::VALIDE->value);
        $otherSaison->setSaison('2025-2026');

        $selected = FetchCoursWishesFormService::selectCurrentForm([$otherSaison], '2026-2027');

        $this->assertNull($selected);
    }
}
