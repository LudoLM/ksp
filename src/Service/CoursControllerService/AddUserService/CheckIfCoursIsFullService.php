<?php

namespace App\Service\CoursControllerService\AddUserService;

use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Enum\StatusCoursEnum;
use App\Repository\StatusCoursRepository;
use App\Service\CoursControllerService\CountUsersInCoursService;
use Symfony\Component\HttpFoundation\Response;

readonly class CheckIfCoursIsFullService
{
    public function __construct(
        private CountUsersInCoursService $countUsersInCoursService,
        private StatusCoursRepository $statusCoursRepository,
    ) {
    }

    public function checkIfCoursIsFull(Cours $cours, int $userCount, bool $isOnWaitingList): void
    {
        // Si le cours est désormais complet, on ne peut plus s'inscrire
        if ($userCount >= $cours->getNbInscriptionMax() && !$isOnWaitingList) {
            throw new \InvalidArgumentException('Le cours est complet', Response::HTTP_FORBIDDEN);
        }
    }

    public function changeStatusIfCoursIsfull(Cours $cours): StatusCours
    {
        // Si désormais le cours est complet, je change le statut du cours
        $usersCount = $this->countUsersInCoursService->countUsers($cours);
        $isFull = $usersCount >= $cours->getNbInscriptionMax();
        if ($isFull && $cours->getStatusCours()->getLibelle() !== StatusCoursEnum::COMPLET->value) {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::COMPLET->value]));
        }

        return $cours->getStatusCours();
    }
}
