<?php

namespace App\Controller\Api;

use App\Entity\Pack;
use App\Entity\User;
use App\Repository\PackRepository;
use App\Service\StripeService\StripeCheckoutCreditService;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Pack')]
class PackController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('api/public/packs', name: 'packs_list', methods: ['GET'])]
    public function index(PackRepository $packRepository): JsonResponse
    {
        $packs = $packRepository->findAll();

        return $this->json($packs, context: ['groups' => 'pack:index']);
    }

    public function show(Pack $pack): JsonResponse
    {
        return $this->json($pack);
    }

    #[Route('api/merci/{id}', name: 'merci', methods: ['POST'])]
    public function merci(string $id, StripeCheckoutCreditService $stripeCheckoutCreditService): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \Exception('L\'utilisateur n\'est pas valide');
        }

        try {
            $result = $stripeCheckoutCreditService->creditUserForCheckoutSession($user, $id);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur lors du traitement du paiement Stripe', [
                'error' => $e->getMessage(),
                'checkout_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->json([
                'type' => 'error',
                'message' => 'Erreur lors du traitement du paiement',
            ], 500);
        }

        if ('forbidden' === $result['status']) {
            return $this->json(['message' => $result['message']], 403);
        }

        if ('not_paid' === $result['status']) {
            return $this->json(['message' => $result['message']], 400);
        }

        if ('already_processed' === $result['status']) {
            return $this->json(['message' => $result['message']], 400);
        }

        return $this->json([
            'message' => $result['message'],
            'userQuantity' => $result['userQuantity'],
        ], status: 200);
    }
}
