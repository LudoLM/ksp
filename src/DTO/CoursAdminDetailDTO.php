<?php

namespace App\DTO;

final readonly class CoursAdminDetailDTO
{
    public function __construct(
        public CoursPublicDetailDTO $cours,
        public array $usersSubscribed,
        public array $usersOnStandby,
    ) {
    }
}
