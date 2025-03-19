<?php

namespace App\Exception;

class FilteringCoursException extends \Exception
{
    public function __construct($message = 'Erreur lors du filtrage des cours', $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
