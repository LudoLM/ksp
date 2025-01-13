<?php

namespace App\Event;

use App\Entity\Cours;
use App\Entity\StatusCours;
use Symfony\Contracts\EventDispatcher\Event;

class UpdateStatusCoursEvent extends Event
{
    public const UPDATE_STATUS_COURS = 'cours.update_status';

    public function __construct()
    {
    }

}
