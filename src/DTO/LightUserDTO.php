<?php

namespace App\DTO;

class LightUserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $prenom,
        public readonly string $nom,
    ) {
    }
}
