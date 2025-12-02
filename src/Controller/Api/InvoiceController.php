<?php

namespace App\Controller\Api;

use App\Entity\HistoriquePaiement;
use App\Repository\HistoriquePaiementRepository;
use App\Security\Voter\UserVoter;
use App\Service\DomPDFService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[OA\Tag(name: 'Invoice')]
class InvoiceController extends AbstractController
{
    #[Route('api/get-invoice-PDF/{id}', name: 'app_invoice_pdf', methods: ['GET'])]
    public function getStreamResponse(HistoriquePaiement $paiement, DomPDFService $domPDFService, HistoriquePaiementRepository $historiquePaiementRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(UserVoter::VIEW, $paiement->getUser());
            $html = $this->renderView('invoice/index.html.twig', [
                'paiement' => $paiement,
            ]);

            return $domPDFService->getStreamResponse($html, 'facture_kss.pdf');
        } catch (AccessDeniedException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_FORBIDDEN);
        }
    }
}
