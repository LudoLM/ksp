<?php

namespace App\DTO;

use App\Entity\UsersCours;
use Symfony\Component\Serializer\Attribute\Groups;

readonly class CoursFillingDTO{
    public function __construct(int $id, \DateTime $dateCours, int $nbInscriptionMax, int $nbInscrits)
    {
        $this->id = $id;
        $this->dateCours = $dateCours;
        $this->nbInscriptionMax = $nbInscriptionMax;
        $this->nbInscrits = $nbInscrits;
    }

    #[Groups('cours_filling:index')]
    public int $id;

    #[Groups('cours_filling:index')]
    public \DateTime $dateCours;

    #[Groups('cours_filling:index')]
    public int $nbInscriptionMax;

    #[Groups('cours_filling:index')]
    public int $nbInscrits;

}
