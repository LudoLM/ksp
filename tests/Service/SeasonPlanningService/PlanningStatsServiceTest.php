<?php

declare(strict_types=1);

namespace App\Tests\Service\SeasonPlanningService;

use App\Helper\SaisonHelper;
use App\Repository\CoursWishesFormRepository;
use App\Service\SeasonPlanningService\PlanningStatsService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(PlanningStatsService::class)]
class PlanningStatsServiceTest extends TestCase
{
    private CoursWishesFormRepository&MockObject $repository;
    private PlanningStatsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CoursWishesFormRepository::class);
        $this->service = new PlanningStatsService($this->repository);
    }

    public function testGetStatsMergesPrimaryAndSecondaryCountsPerSlot(): void
    {
        $this->repository->method('countByCreneauPrimaire')->with(SaisonHelper::current())->willReturn([12 => 5, 14 => 2]);
        $this->repository->method('countByCreneauSecondaire')->with(SaisonHelper::current())->willReturn([12 => 1, 20 => 3]);

        $stats = $this->service->getStatsForCurrentSaison();

        $bySlotId = array_column($stats, null, 'slotId');
        $this->assertSame(['slotId' => 12, 'primaryCount' => 5, 'secondaryCount' => 1], $bySlotId[12]);
        $this->assertSame(['slotId' => 14, 'primaryCount' => 2, 'secondaryCount' => 0], $bySlotId[14]);
        $this->assertSame(['slotId' => 20, 'primaryCount' => 0, 'secondaryCount' => 3], $bySlotId[20]);
        $this->assertCount(3, $stats);
    }

    public function testGetStatsReturnsEmptyArrayWhenNoFormsExistYet(): void
    {
        $this->repository->method('countByCreneauPrimaire')->willReturn([]);
        $this->repository->method('countByCreneauSecondaire')->willReturn([]);

        $this->assertSame([], $this->service->getStatsForCurrentSaison());
    }
}
