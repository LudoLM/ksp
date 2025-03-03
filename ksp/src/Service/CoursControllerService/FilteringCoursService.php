<?php

namespace App\Service\CoursControllerService;

use App\Exception\FilteringCoursException;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;

class FilteringCoursService
{

    public function __construct(
        private CoursRepository $coursRepository,
        private TypeCoursRepository $typeCoursRepository,
        private StatusCoursRepository $statusCoursRepository
    ) {
    }

    public function filterCours(int $currentPage, int $maxPerPage,  int $typeCoursId, string $dateCoursStr, int $statusCoursId, string $route, bool $isAdminPath): array
    {
        // Récupérer l'entité TypeCours si `typeCours` est fourni
        $typeCours = null;
        if ($typeCoursId && $typeCoursId !== 0) {
            $typeCours = $this->typeCoursRepository->findOneBy(['id' => $typeCoursId]);
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
            $dateCours->modify('+' . ($currentPage - 1) * 7 . ' days');
            $dateCours->modify('monday this week');
            $dateLimit = clone $dateCours;
            $dateLimit->modify('+6 days');
        }


        $statusCours = null;
        if ($statusCoursId && $statusCoursId !== 0) {
            $statusCours = $this->statusCoursRepository->findOneBy(['id' => $statusCoursId]);
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
