<?php

declare(strict_types=1);

namespace App\Service\SeasonPlanningService;

use App\Entity\SeasonPlanning;
use App\Entity\SeasonPlanningSlot;
use App\Helper\SaisonHelper;
use App\Repository\SeasonPlanningRepository;
use App\Repository\SeasonPlanningSlotRepository;

readonly class FetchCurrentSeasonPlanningSlotsService
{
    public function __construct(
        private SeasonPlanningRepository $planningRepository,
        private SeasonPlanningSlotRepository $slotRepository,
    ) {
    }

    /**
     * @return SeasonPlanningSlot[]
     */
    public function fetch(): array
    {
        $planning = $this->planningRepository->findBySaison(SaisonHelper::current());
        if (!$planning instanceof SeasonPlanning) {
            return [];
        }

        return $this->slotRepository->findAllForSeasonPlanning($planning);
    }
}
