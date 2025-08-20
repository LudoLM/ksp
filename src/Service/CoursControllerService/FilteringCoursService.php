<?php

namespace App\Service\CoursControllerService;

use App\Entity\Cours;
use App\Exception\FilteringCoursException;
use App\Helper\DateHelper;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;

readonly class FilteringCoursService
{
    public function __construct(
        private CoursRepository $coursRepository,
        private TypeCoursRepository $typeCoursRepository,
        private StatusCoursRepository $statusCoursRepository,
    ) {
    }

    public function filterCours(
        int $typeCoursId,
        string $dateCoursStr,
        int $statusCoursId,
        string $route,
        bool $isOpenRequired,
        bool $isPrioritized,
        bool $isAdmin,
    ): array {
        $typeCours = null;
        if (0 !== $typeCoursId) {
            try {
                $typeCours = $this->typeCoursRepository->findOneBy(['id' => $typeCoursId]);
                if (null === $typeCours) {
                    throw new FilteringCoursException('Le type de cours fourni est invalide', 400);
                }
            } catch (\Exception $exception) {
                throw new FilteringCoursException('Le type de cours fourni est invalide', 400, $exception);
            }
        }

        $statusCours = null;
        if (0 !== $statusCoursId) {
            try {
                $statusCours = $this->statusCoursRepository->findOneBy(['id' => $statusCoursId]);
                if (null === $statusCours) {
                    throw new FilteringCoursException('Le status de cours fourni est invalide', 400);
                }
            } catch (\Exception $e) {
                throw new FilteringCoursException('Le statut fourni est invalide', 400, $e);
            }
        }

        // Convertir la chaîne de date en \DateTime si `dateCours` est fourni
        $dateCoursStr = 'null' === $dateCoursStr ? null : $dateCoursStr;
        $dateCours = null;
        if (null !== $dateCoursStr && '' !== $dateCoursStr && '0' !== $dateCoursStr) {
            try {
                $dateCours = new \DateTime($dateCoursStr);
            } catch (\Exception $e) {
                throw new FilteringCoursException('La date fournie est invalide', 400, $e);
            }
        }

        $dateLimit = null;
        if ('cours_next_cours' === $route) {
            if ($isOpenRequired) {
                $getFirstCours = $this->coursRepository->findNextCours($typeCours, $dateCours, $isPrioritized, $statusCours, $isAdmin);
                if (!$getFirstCours instanceof Cours) {
                    if (null === $typeCours && null === $statusCours) {
                        throw new FilteringCoursException('Aucun cours prévu à partir de cette date', 404);
                    }
                    if (null === $typeCours && null !== $statusCours) {
                        throw new FilteringCoursException('Aucun cours '.$statusCours->getLibelle().' prévu à partir de cette date', 404);
                    }
                    if (null !== $typeCours && null === $statusCours) {
                        throw new FilteringCoursException('Aucun cours de '.$typeCours->getLibelle().' prévu à partir de cette date', 404);
                    }
                    throw new FilteringCoursException('Aucun cours de '.$typeCours->getLibelle().' '.$statusCours->getLibelle().' prévu à partir de cette date', 404);
                }

                return [
                    'type' => 'info_next_cours',
                    'typeCours' => $getFirstCours->getTypeCours()->getLibelle(),
                    'statusCours' => $getFirstCours->getStatusCours()->getLibelle(),
                    'nextCoursDate' => $getFirstCours->getDateCours(),
                ];
            }

            [$dateCours, $dateLimit] = DateHelper::adjustDatesForCalendarRoute($dateCours);

            return $this->coursRepository->findAllSortByDateForUsers($typeCours, $dateCours, $dateLimit, $isPrioritized, $isAdmin, $statusCours);
        }
        if ('cours_calendar' === $route) {
            if ($isOpenRequired) {
                $getFirstCours = $this->coursRepository->findNextCours($typeCours, $dateCours, $isPrioritized, $statusCours, $isAdmin);
                if (!$getFirstCours instanceof Cours) {
                    throw new FilteringCoursException('Aucun cours de '.$typeCours->getLibelle().' '.$statusCours->getLibelle().' prévu à partir de cette date', 404);
                }

                return [$getFirstCours];
            }

            [$dateCours, $dateLimit] = DateHelper::adjustDatesForCalendarRoute($dateCours);

            return $this->coursRepository->findAllSortByDateForUsers($typeCours, $dateCours, $dateLimit, $isPrioritized, $isAdmin, $statusCours);
        }
        [$dateCours, $dateLimit] = DateHelper::adjustDatesForIndexRoute($dateCours);

        return $this->coursRepository->findAllSortByDate($typeCours, $dateCours, $dateLimit, $statusCours);
    }
}
