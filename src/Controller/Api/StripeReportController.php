<?php

namespace App\Controller\Api;

use App\Service\StripeService\BalanceStripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class StripeReportController extends AbstractController
{
    public function __construct(
        private readonly BalanceStripeService $balanceStripeService,
    ) {
    }

    #[Route('/api/stripe-report', name: 'stripe_report', methods: ['GET'])]
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
