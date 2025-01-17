<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class LightUserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $prenom,
        public readonly string $nom
    )
    {
    }
}
