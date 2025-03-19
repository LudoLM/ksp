<?php

namespace App\Controller\Api;

use App\Repository\HistoriquePaiementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class HistoriquePaiementController extends AbstractController
{
    public function __construct(
        private readonly HistoriquePaiementRepository $historiquePaiementRepository,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route('api/historiquePaiements', name: 'get_historique_paiements', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        try {
            $startDate = new \DateTime($startDate);
            $endDate = new \DateTime($endDate);
        } catch (\Exception) {
            return new JsonResponse(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        $allPaiements = $this->historiquePaiementRepository->findQuantityOfEachPacksPerMonth($startDate, $endDate);
        $responseData = $this->serializer->serialize($allPaiements, 'json', ['groups' => 'historique_paiements:index']);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }
}
