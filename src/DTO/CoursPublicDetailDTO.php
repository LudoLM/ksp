<?php

namespace App\DTO;

final readonly class CoursPublicDetailDTO
{
    public function __construct(
        public int $id,
        public string $dateCours,
        public ?string $launchedAt,
        public StatusCoursDTO $statusCours,
        public TypeCoursDTO $typeCours,
        public int $duree,
        public int $nbInscriptionMax,
        public bool $hasPriority,
        public bool $hasLimitOfOneCoursPerWeek,
        public ?string $specialNote,
        public int $activeSubscribedCount,
        public int $remainingSlots,
        public bool $isSubscribed,
        public bool $isUserOnWaitingList,
    ) {
    }
}
