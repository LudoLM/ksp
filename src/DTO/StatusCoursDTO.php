<?php

namespace App\DTO;

final readonly class StatusCoursDTO
{
    public function __construct(
        public int $id,
        public string $libelle,
    ) {
    }
}
