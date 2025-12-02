<?php

namespace App\Controller\Api;

use App\Service\StripeService\BalanceStripeService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'StripeReport')]
class StripeReportController extends AbstractController
{
    public function __construct(
        private readonly BalanceStripeService $balanceStripeService,
    ) {
    }

    #[Route('/api/admin/stripe-report', name: 'stripe_report', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous devez être admin pour consulter les reports')]
    public function getRevenue(
        #[MapQueryParameter] int $month,
        #[MapQueryParameter] int $year,
    ): JsonResponse {
        try {
            $dataStripe = $this->balanceStripeService->getBalance($month, $year);

            return $this->json($dataStripe);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
