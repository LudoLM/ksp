<?php

namespace App\Service;

use App\DTO\CoursAdminDetailDTO;
use App\DTO\CoursPublicDetailDTO;
use App\DTO\LightUserDTO;
use App\DTO\StatusCoursDTO;
use App\DTO\TypeCoursDTO;
use App\DTO\UsersCoursDTO;
use App\Entity\Cours;
use App\Entity\User;

class CoursDetailsMapperService
{
    public function getCoursPublicDetailDTO(Cours $cours, ?User $currentUser): CoursPublicDetailDTO
    {
        $statusCours = $cours->getStatusCours();
        $typeCours = $cours->getTypeCours();

        $activeSubscribedCount = $cours->getActiveSubscribedCount();
        $remainingSlots = max($cours->getNbInscriptionMax() - $activeSubscribedCount, 0);

        $usersCours = $cours->getUsersCours()->toArray();
        $isSubscribed = false;
        $isUserOnWaitingList = false;
        if ($currentUser instanceof User) {
            foreach ($usersCours as $userCours) {
                if ($userCours->getUser()->getId() === $currentUser->getId()) {
                    $isUserOnWaitingList = $userCours->isOnWaitingList() ?? false;
                    $isSubscribed = !$isUserOnWaitingList;
                    break;
                }
            }
        }

        return new CoursPublicDetailDTO(
            id: $cours->getId() ?? 0,
            dateCours: $cours->getDateCours()->format(\DateTimeInterface::ATOM),
            launchedAt: $cours->getLaunchedAt()->format(\DateTimeInterface::ATOM),
            statusCours: new StatusCoursDTO(
                id: $statusCours->getId() ?? 0,
                libelle: $statusCours->getLibelle() ?? '',
            ),
            typeCours: new TypeCoursDTO(
                id: $typeCours->getId() ?? 0,
                libelle: $typeCours->getLibelle() ?? '',
                descriptif: $typeCours->getDescriptif() ?? '',
                thumbnail: $typeCours->getThumbnail() ?? '',
            ),
            duree: $cours->getDuree(),
            nbInscriptionMax: $cours->getNbInscriptionMax(),
            hasPriority: $cours->hasPriority(),
            hasLimitOfOneCoursPerWeek: $cours->hasLimitOfOneCoursPerWeek(),
            specialNote: $cours->getSpecialNote() ?? null,
            activeSubscribedCount: $activeSubscribedCount,
            remainingSlots: $remainingSlots,
            isSubscribed: $isSubscribed,
            isUserOnWaitingList: $isUserOnWaitingList,
        );
    }

    public function getCoursAdminDetailDTO(Cours $cours, ?User $currentUser): CoursAdminDetailDTO
    {
        $publicDto = $this->getCoursPublicDetailDTO($cours, $currentUser);

        $usersSubscribed = [];
        $usersOnStandby = [];
        foreach ($cours->getUsersCours() as $userCours) {
            $user = $userCours->getUser();
            $userDto = new LightUserDTO(
                id: $user->getId() ?? 0,
                prenom: $user->getPrenom(),
                nom: $user->getNom() ?? '',
            );
            $usersCoursDto = new UsersCoursDTO(
                id: $userCours->getId() ?? 0,
                isOnWaitingList: $userCours->isOnWaitingList() ?? false,
                user: $userDto,
            );

            if ($usersCoursDto->isOnWaitingList) {
                $usersOnStandby[] = $usersCoursDto;
            } else {
                $usersSubscribed[] = $usersCoursDto;
            }
        }

        return new CoursAdminDetailDTO(
            cours: $publicDto,
            usersSubscribed: $usersSubscribed,
            usersOnStandby: $usersOnStandby,
        );
    }
}
