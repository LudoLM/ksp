<?php

namespace App\Service\CoursControllerService;

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

        $statusCours = null;
        if (0 !== $statusCoursId) {
            try {
                $statusCours = $this->statusCoursRepository->findOneBy(['id' => $statusCoursId]);
            } catch (\Exception $e) {
                throw new FilteringCoursException('Le statut fourni est invalide', 400, $e);
            }
        }

        $dateLimit = null;
        // recupere le 1er jour de la semaine de la variable dateCours si la route est getCoursCalendar
        if ('cours_calendar' === $route) {
            [$dateCours, $dateLimit] = DateHelper::adjustDatesForCalendarRoute($dateCours);

            return $this->coursRepository->findAllSortByDateForUsers($typeCours, $dateCours, $dateLimit, $statusCours);
        }
        [$dateCours, $dateLimit] = DateHelper::adjustDatesForIndexRoute($dateCours);

        return $this->coursRepository->findAllSortByDate($typeCours, $dateCours, $dateLimit, $statusCours);
    }
}
