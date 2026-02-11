<?php

namespace App\DTO;

final readonly class TypeCoursDTO
{
    public function __construct(
        public int $id,
        public string $libelle,
        public string $descriptif,
        public string $thumbnail,
    ) {
    }
}
