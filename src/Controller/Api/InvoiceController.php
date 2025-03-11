<?php

namespace App\Controller\Api;

use App\Repository\HistoriquePaiementRepository;
use App\Service\DomPDFService;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'api/', name: 'api_')]
class InvoiceController extends AbstractController
{


    #[Route('getInvoicePDF', name: 'app_invoice_pdf')]
    public function getStreamResponse(Request $request, DomPDFService $domPDFService, HistoriquePaiementRepository $historiquePaiementRepository) : Response
    {


        $payload = json_decode($request->getContent(), true);
        $paiement = $historiquePaiementRepository->find($payload['paiementId']);

        if (!$paiement) {
            return new Response('Paiement non trouvé', Response::HTTP_NOT_FOUND);
        }
        if ($this->getUser() !== $paiement->getUser()) {
            return new Response('Non autorisé', Response::HTTP_UNAUTHORIZED);
        }

        $html = $this->renderView('invoice/index.html.twig', [
            'paiement' => $paiement
        ]);

        return $domPDFService->getStreamResponse($html,'facture_kss.pdf');

    }
}
