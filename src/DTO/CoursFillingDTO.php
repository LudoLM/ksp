<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\Groups;

readonly class CoursFillingDTO
{
    public function __construct(#[Groups('cours_filling:index')]
        public int $id, #[Groups('cours_filling:index')]
        public \DateTime $dateCours, #[Groups('cours_filling:index')]
        public int $nbInscriptionMax, #[Groups('cours_filling:index')]
        public int $nbInscrits)
    {
    }
}
