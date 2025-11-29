<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Helper\DateHelper;
use App\Repository\HistoriquePaiementRepository;
use App\Repository\UsersCoursRepository;
use App\Service\NotificationService\NotificationsUsersActionsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class AdminUserActionsController extends AbstractController
{
    public function __construct(
        private readonly UsersCoursRepository $usersCoursRepository,
        private readonly HistoriquePaiementRepository $historiquePaiementRepository,
        private readonly NotificationsUsersActionsService $actionsService,
    ) {
    }

    #[Route('/api/getLastUsersActions', name: 'admin_user_actions_index')]
    public function getLastActivities(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }
        $usersCours = $this->usersCoursRepository->getLastActivities($user);
        $historique_paiements = $this->historiquePaiementRepository->getPaymentSinceLastVisit($user);
        $activities = $this->actionsService->toNotification($usersCours, $historique_paiements);

        // Limitation à 10 résultats
        $activities = array_slice($activities, 0, 10);

        return $this->json($activities);
    }

    #[Route('/api/getUsersActionsPerMonth', name: 'admin_user_actions_per_month')]
    public function getActivitiesPerMonth(
        #[MapQueryParameter] int $month,
        #[MapQueryParameter] int $year,
        #[MapQueryParameter] string $userName,
    ): JsonResponse {
        ++$month;
        [$start, $end] = DateHelper::adjustDatesForIndexRoute(new \DateTime("{$year}-{$month}-01 00:00:00"));

        $usersCours = $this->usersCoursRepository->getLastActivitiesPerMonth($start, $end, $userName);
        $historique_paiements = $this->historiquePaiementRepository->getPaymentPerMonth($start, $end, $userName);
        $activities = $this->actionsService->toNotification($usersCours, $historique_paiements);

        return $this->json($activities);
    }
}
