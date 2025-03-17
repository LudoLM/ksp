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
        private CoursRepository       $coursRepository,
        private TypeCoursRepository   $typeCoursRepository,
        private StatusCoursRepository $statusCoursRepository
    ) {
    }

    public function filterCours(
        int $currentPage,
        int $maxPerPage,
        int $typeCoursId,
        string $dateCoursStr,
        int $statusCoursId,
        string $route,
        bool $isAdminPath
    ): array
    {
        if ($currentPage <= 0 || $maxPerPage <= 0) {
            throw new \InvalidArgumentException("Les paramètres de pagination doivent être supérieurs à zéro.");
        }
        // Récupérer l'entité TypeCours si `typeCours` est fourni
        $typeCours = null;
        if ($typeCoursId && $typeCoursId !== 0) {
            try {
                $typeCours = $this->typeCoursRepository->findOneBy(['id' => $typeCoursId]);
                if ($typeCours === null) {
                    throw new FilteringCoursException("Le type de cours fourni est invalide", 400);
                }
            } catch (\Exception $exception) {
                throw new FilteringCoursException("Le type de cours fourni est invalide", 400, $exception);
            }
        }


        // Convertir la chaîne de date en \DateTime si `dateCours` est fourni
        $dateCoursStr = $dateCoursStr === "null" ? null : $dateCoursStr;
        $dateCours = null;
        if ($dateCoursStr) {
            try {
                $dateCours = new \DateTime($dateCoursStr);
            } catch (\Exception $e) {
                throw new FilteringCoursException("La date fournie est invalide", 400, $e);
            }
        }

        $dateLimit = null;
        // recupere le 1er jour de la semaine de la variable dateCours si la route est getCoursCalendar
        if ($route === 'api_cours_calendar') {
            [$dateCours, $dateLimit] = DateHelper::adjustDatesForCalendarRoute($dateCours, $currentPage);
        }


        $statusCours = null;
        if ($statusCoursId && $statusCoursId !== 0) {
            try {
                $statusCours = $this->statusCoursRepository->findOneBy(['id' => $statusCoursId]);
            } catch (\Exception $e) {
                throw new FilteringCoursException("Le statut fourni est invalide", 400, $e);
            }
        }


        // Appeler le repository avec la pagination et les filtres
        if ($isAdminPath) {

            $coursPaginator = $this->coursRepository->findAllSortByDate($currentPage, $maxPerPage, $typeCours, $dateCours, $dateLimit, $statusCours);
        } else {
            $coursPaginator = $this->coursRepository->findAllSortByDateForUsers($currentPage, $maxPerPage, $typeCours, $dateCours, $dateLimit, $statusCours);
        }

        // Récupérer les cours
        $cours = iterator_to_array($coursPaginator);

        // Calculer les métadonnées de pagination
        $totalItems = count($coursPaginator);
        $totalPages = ceil($totalItems / $maxPerPage);

        // Préparer la réponse
        $responseData = [
            'data' => $cours,
            'pagination' => [
                'currentPage' => $currentPage,
                'maxPerPage' => $maxPerPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
            ],
        ];

        return $responseData;
    }

}
