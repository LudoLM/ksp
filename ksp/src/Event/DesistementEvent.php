<?php

namespace App\Event;


use App\Entity\Cours;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Contracts\EventDispatcher\Event;

class DesistementEvent extends Event
{
    /**
     * @Event("Symfony\Contracts\EventDispatcher\Event")
     */
    public const DESISTEMENT_COURS = 'cours.desistement';

    public function __construct(private Cours $cours)
    {
    }

    public function getCours(): Cours
    {
        return $this->cours;
    }
}