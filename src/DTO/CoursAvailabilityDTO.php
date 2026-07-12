<?php

namespace App\DTO;

readonly class CoursAvailabilityDTO
{
    public function __construct(
        public int $id,
        public string $typeCoursLibelle,
        public \DateTimeInterface $dateCours,
        public int $duree,
    ) {
    }
}
