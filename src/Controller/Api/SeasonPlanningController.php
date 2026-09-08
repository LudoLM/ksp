<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\AddSeasonPlanningSlotDTO;
use App\Repository\TypeCoursRepository;
use App\Service\SeasonPlanningService\AddSlotService;
use App\Service\SeasonPlanningService\FetchCurrentSeasonPlanningSlotsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SeasonPlanningController extends AbstractController
{
    public function __construct(
        private readonly TypeCoursRepository $typeCoursRepository,
        private readonly AddSlotService $addSlotService,
        private readonly FetchCurrentSeasonPlanningSlotsService $fetchCurrentSeasonPlanningSlotsService,
    ) {
    }

    #[Route('api/public/season-planning/current', name: 'api_season_planning_current', methods: ['GET'])]
    public function current(): JsonResponse
    {
        return $this->json($this->fetchCurrentSeasonPlanningSlotsService->fetch(), Response::HTTP_OK, [], ['groups' => 'season_planning:index']);
    }

    #[Route('api/admin/season-planning', name: 'api_admin_season_planning_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): JsonResponse
    {
        return $this->json($this->fetchCurrentSeasonPlanningSlotsService->fetch(), Response::HTTP_OK, [], ['groups' => 'season_planning:index']);
    }

    #[Route('api/admin/season-planning/slots', name: 'api_admin_season_planning_add_slot', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addSlot(#[MapRequestPayload] AddSeasonPlanningSlotDTO $dto): JsonResponse
    {
        $typeCours = $this->typeCoursRepository->find($dto->typeCoursId);
        if (null === $typeCours) {
            return new JsonResponse(['error' => 'Type de cours introuvable'], Response::HTTP_BAD_REQUEST);
        }

        $timeSelected = \DateTime::createFromFormat('H:i', $dto->timeSelected);
        // createFromFormat() tolère les valeurs hors plage (ex: "25:99") en les
        // faisant déborder sur le jour suivant au lieu d'échouer : il faut donc
        // aussi vérifier getLastErrors(), pas seulement le type de retour.
        $parseErrors = \DateTime::getLastErrors();
        $hasParseIssues = false !== $parseErrors && ($parseErrors['error_count'] > 0 || $parseErrors['warning_count'] > 0);
        if (!$timeSelected instanceof \DateTime || $hasParseIssues) {
            return new JsonResponse(['error' => 'Heure invalide'], Response::HTTP_BAD_REQUEST);
        }

        $slot = $this->addSlotService->addSlot($dto->daySelected, $timeSelected, $typeCours);

        return $this->json($slot, Response::HTTP_CREATED, [], ['groups' => 'season_planning:index']);
    }
}
