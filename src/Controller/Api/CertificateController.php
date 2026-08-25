<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\CertificatMedical;
use App\Entity\User;
use App\Enum\StatusCertificateEnum;
use App\Service\CertificateControllerService\FetchCertificateService;
use App\Service\CertificateControllerService\UploadCertificateService;
use App\Service\CertificateControllerService\ValidateCertificateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CertificateController extends AbstractController
{
    private const int LIMIT_PER_PAGE = 15;

    public function __construct(
        private readonly UploadCertificateService $uploadCertificateService,
        private readonly FetchCertificateService $fetchCertificateService,
        private readonly ValidateCertificateService $validateCertificateService,
    ) {
    }

    #[Route('/api/certificate/upload', name: 'api_certificate_upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié');
        }

        $file = $request->files->get('certificate');
        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'Aucun fichier envoyé'], 400);
        }

        try {
            $this->uploadCertificateService->validate($file);
            $this->uploadCertificateService->upload($user, $file);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Throwable) {
            return new JsonResponse(['error' => 'Erreur lors de la sauvegarde.'], 500);
        }

        return $this->json(['status' => StatusCertificateEnum::PENDING->value], 201);
    }

    #[Route('/api/admin/certificates/pending', name: 'api_admin_certificates_pending', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listPending(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));

        return new JsonResponse(
            $this->fetchCertificateService->getPendingCertificates($page, self::LIMIT_PER_PAGE),
            Response::HTTP_OK
        );
    }

    #[Route('/api/admin/certificate/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function viewCertificate(CertificatMedical $certificate): Response
    {
        try {
            $content = $this->fetchCertificateService->readCertificateContent($certificate);
        } catch (\RuntimeException) {
            throw $this->createNotFoundException('Fichier introuvable.');
        }

        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    #[Route('/api/admin/certificate/{id}/validate', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function validateCertificate(CertificatMedical $certificate, Request $request): JsonResponse
    {
        $action = (string) $request->request->get('action');
        $reason = $request->request->get('reason');
        $reason = null !== $reason ? trim((string) $reason) : null;

        if ('approve' !== $action && (null === $reason || '' === $reason)) {
            return new JsonResponse(['error' => 'Le motif du refus est requis.'], 400);
        }

        $this->validateCertificateService->updateStatus($certificate, $action, $reason);

        return $this->json(['status' => $certificate->getStatus()]);
    }
}
