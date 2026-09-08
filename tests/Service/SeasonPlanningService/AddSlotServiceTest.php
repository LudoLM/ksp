<?php

declare(strict_types=1);

namespace App\Tests\Service\SeasonPlanningService;

use App\Entity\SeasonPlanning;
use App\Entity\SeasonPlanningSlot;
use App\Entity\TypeCours;
use App\Helper\SaisonHelper;
use App\Repository\SeasonPlanningRepository;
use App\Repository\SeasonPlanningSlotRepository;
use App\Service\SeasonPlanningService\AddSlotService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Query;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddSlotService::class)]
class AddSlotServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private SeasonPlanningRepository&MockObject $planningRepository;
    private SeasonPlanningSlotRepository&MockObject $slotRepository;
    private AddSlotService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->planningRepository = $this->createMock(SeasonPlanningRepository::class);
        $this->slotRepository = $this->createMock(SeasonPlanningSlotRepository::class);
        $this->service = new AddSlotService($this->em, $this->planningRepository, $this->slotRepository);
    }

    public function testAddSlotCreatesTheSeasonPlanningAndTheSlotWhenNoneExistsForTheCurrentSaison(): void
    {
        $typeCours = new TypeCours();
        $this->planningRepository->method('findBySaison')->with(SaisonHelper::current())->willReturn(null);
        // findByDayTime is called once the new planning has been flushed (and thus has an id).
        $this->slotRepository->expects($this->once())->method('findByDayTime')->willReturn(null);

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function ($entity) use (&$persisted): void {
            $persisted[] = $entity;
        });
        // Once to give the new SeasonPlanning an id, once for the new slot.
        $this->em->expects($this->exactly(2))->method('flush');

        $timeSelected = new \DateTime('18:00');
        $slot = $this->service->addSlot(4, $timeSelected, $typeCours);

        $this->assertCount(2, $persisted, 'expected both the new SeasonPlanning and the new SeasonPlanningSlot to be persisted');
        $this->assertInstanceOf(SeasonPlanning::class, $persisted[0]);
        $this->assertSame(SaisonHelper::current(), $persisted[0]->getSaison());
        $this->assertSame($slot, $persisted[1]);
        $this->assertSame(4, $slot->getDaySelected());
        $this->assertTrue($slot->getTypeCoursOptions()->contains($typeCours));
    }

    public function testAddSlotReusesTheSeasonPlanningCreatedConcurrentlyWhenFlushHitsTheUniqueConstraint(): void
    {
        $typeCours = new TypeCours();
        $concurrentlyCreatedPlanning = new SeasonPlanning();
        $concurrentlyCreatedPlanning->setSaison(SaisonHelper::current());

        $this->planningRepository->method('findBySaison')
            ->willReturnOnConsecutiveCalls(null, $concurrentlyCreatedPlanning);
        $this->slotRepository->method('findByDayTime')->willReturn(null);

        $driverException = $this->createStub(\Doctrine\DBAL\Driver\Exception::class);
        $uniqueConstraintViolation = new UniqueConstraintViolationException($driverException, new Query('INSERT', [], []));

        $flushCalls = 0;
        $this->em->method('flush')->willReturnCallback(function () use (&$flushCalls, $uniqueConstraintViolation): void {
            ++$flushCalls;
            if (1 === $flushCalls) {
                throw $uniqueConstraintViolation;
            }
        });
        $this->em->expects($this->once())->method('detach');

        $slot = $this->service->addSlot(4, new \DateTime('18:00'), $typeCours);

        $this->assertSame($concurrentlyCreatedPlanning, $slot->getSeasonPlanning());
        $this->assertSame(2, $flushCalls, 'expected one failed flush for our insert, one successful flush for the slot');
    }

    public function testAddSlotReusesTheExistingSeasonPlanningForTheCurrentSaison(): void
    {
        $typeCours = new TypeCours();
        $existingPlanning = new SeasonPlanning();
        $existingPlanning->setSaison(SaisonHelper::current());

        $this->planningRepository->method('findBySaison')->willReturn($existingPlanning);
        $this->slotRepository->method('findByDayTime')->with($existingPlanning, 4, $this->anything())->willReturn(null);

        $persisted = [];
        $this->em->method('persist')->willReturnCallback(function ($entity) use (&$persisted): void {
            $persisted[] = $entity;
        });

        $slot = $this->service->addSlot(4, new \DateTime('18:00'), $typeCours);

        $this->assertCount(1, $persisted, 'no new SeasonPlanning should be created when one already exists');
        $this->assertSame($existingPlanning, $slot->getSeasonPlanning());
    }

    public function testAddSlotReusesTheExistingSlotAndAddsANewOptionWhenTheDayTimeAlreadyExists(): void
    {
        $firstTypeCours = new TypeCours();
        $secondTypeCours = new TypeCours();

        $existingPlanning = new SeasonPlanning();
        $existingSlot = new SeasonPlanningSlot();
        $existingSlot->setSeasonPlanning($existingPlanning);
        $existingSlot->setDaySelected(4);
        $existingSlot->addTypeCoursOption($firstTypeCours);

        $this->planningRepository->method('findBySaison')->willReturn($existingPlanning);
        $this->slotRepository->method('findByDayTime')->willReturn($existingSlot);
        $this->em->expects($this->never())->method('persist');

        $slot = $this->service->addSlot(4, new \DateTime('18:00'), $secondTypeCours);

        $this->assertSame($existingSlot, $slot);
        $this->assertCount(2, $slot->getTypeCoursOptions());
        $this->assertTrue($slot->getTypeCoursOptions()->contains($firstTypeCours));
        $this->assertTrue($slot->getTypeCoursOptions()->contains($secondTypeCours));
    }

    public function testAddSlotIsIdempotentWhenTheSameTypeCoursIsAddedTwiceToTheSameSlot(): void
    {
        $typeCours = new TypeCours();
        $existingPlanning = new SeasonPlanning();
        $existingSlot = new SeasonPlanningSlot();
        $existingSlot->setSeasonPlanning($existingPlanning);
        $existingSlot->setDaySelected(4);
        $existingSlot->addTypeCoursOption($typeCours);

        $this->planningRepository->method('findBySaison')->willReturn($existingPlanning);
        $this->slotRepository->method('findByDayTime')->willReturn($existingSlot);

        $slot = $this->service->addSlot(4, new \DateTime('18:00'), $typeCours);

        $this->assertCount(1, $slot->getTypeCoursOptions());
    }
}
