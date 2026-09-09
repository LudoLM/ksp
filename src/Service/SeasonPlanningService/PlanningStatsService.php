<?php

declare(strict_types=1);

namespace App\Service\SeasonPlanningService;

use App\Helper\SaisonHelper;
use App\Repository\CoursWishesFormRepository;

readonly class PlanningStatsService
{
    public function __construct(
        private CoursWishesFormRepository $repository,
    ) {
    }

    /**
     * @return array<int, array{slotId: int, primaryCount: int, secondaryCount: int}>
     */
    public function getStatsForCurrentSaison(): array
    {
        $saison = SaisonHelper::current();
        $primaryCounts = $this->repository->countByCreneauPrimaire($saison);
        $secondaryCounts = $this->repository->countByCreneauSecondaire($saison);

        $slotIds = array_unique(array_merge(array_keys($primaryCounts), array_keys($secondaryCounts)));

        $stats = [];
        foreach ($slotIds as $slotId) {
            $stats[] = [
                'slotId' => $slotId,
                'primaryCount' => $primaryCounts[$slotId] ?? 0,
                'secondaryCount' => $secondaryCounts[$slotId] ?? 0,
            ];
        }

        return $stats;
    }
}
