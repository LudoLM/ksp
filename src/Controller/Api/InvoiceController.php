<?php

namespace App\Controller\Api;

use App\Repository\HistoriquePaiementRepository;
use App\Security\Voter\UserVoter;
use App\Service\DomPDFService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class InvoiceController extends AbstractController
{
    #[Route('api/getInvoicePDF', name: 'app_invoice_pdf')]
    public function getStreamResponse(Request $request, DomPDFService $domPDFService, HistoriquePaiementRepository $historiquePaiementRepository): Response
    {
        try {
            $payload = json_decode($request->getContent(), true);
            $paiement = $historiquePaiementRepository->find($payload['paiementId']);

            if (null === $paiement) {
                return new Response('Paiement non trouvé', Response::HTTP_NOT_FOUND);
            }
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
