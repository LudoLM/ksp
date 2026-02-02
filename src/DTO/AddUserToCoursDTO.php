<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO pour l'inscription d'un utilisateur à un cours.
 *
 * Utilisé par l'endpoint POST /api/add-user.
 * Valide automatiquement les types et valeurs via MapRequestPayload.
 *
 * @see \App\Controller\Api\CoursController::addUserToCours()
 */
class AddUserToCoursDTO
{
    /**
     * ID de l'utilisateur à inscrire (null = utilisateur connecté).
     */
    #[Assert\Type(type: 'integer', message: 'userId doit être un entier')]
    #[Assert\Positive(message: 'userId doit être positif')]
    public ?int $userId = null;

    #[Assert\NotBlank(message: 'coursId est requis')]
    #[Assert\Type(type: 'integer', message: 'coursId doit être un entier')]
    #[Assert\Positive(message: 'coursId doit être positif')]
    public int $coursId;

    #[Assert\NotNull(message: 'isOnWaitingList est requis')]
    #[Assert\Type(type: 'bool', message: 'isOnWaitingList doit être un booléen')]
    public bool $isOnWaitingList;
}
