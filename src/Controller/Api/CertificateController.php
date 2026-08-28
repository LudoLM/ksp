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
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CertificateController extends AbstractController
{
    private const int LIMIT_PER_PAGE = 15;

    public function __construct(
        private readonly UploadCertificateService $uploadCertificateService,
        private readonly FetchCertificateService $fetchCertificateService,
        private readonly ValidateCertificateService $validateCertificateService,
        private readonly RateLimiterFactory $certificateUploadLimiter,
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

        return $this->handleUpload($user, $request, null);
    }

    #[Route('/api/admin/users/{id}/certificate/upload', name: 'api_admin_certificate_upload_for_user', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function uploadForUser(User $targetUser, Request $request): JsonResponse
    {
        $admin = $this->getUser();
        if (!$admin instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié');
        }

        return $this->handleUpload($targetUser, $request, $admin);
    }

    private function handleUpload(User $targetUser, Request $request, ?User $uploadedBy): JsonResponse
    {
        $limiter = $this->certificateUploadLimiter->create((string) $targetUser->getId());
        if (!$limiter->consume(1)->isAccepted()) {
            return new JsonResponse(['error' => 'Trop de tentatives. Veuillez réessayer plus tard.'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $file = $request->files->get('certificate');
        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'Aucun fichier envoyé'], 400);
        }

        try {
            $this->uploadCertificateService->validate($file);
            $this->uploadCertificateService->upload($targetUser, $file, $uploadedBy);
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
        if (StatusCertificateEnum::PENDING->value !== $certificate->getStatus()) {
            return new JsonResponse(['error' => 'Ce certificat a déjà été traité.'], Response::HTTP_CONFLICT);
        }

        $action = $request->request->getString('action');

        if (!\in_array($action, ['approve', 'reject'], true)) {
            return new JsonResponse(['error' => 'Action invalide.'], 400);
        }

        if ('approve' === $action) {
            $validUntilRaw = $request->request->getString('validUntil');
            $validUntil = \DateTimeImmutable::createFromFormat('Y-m-d', $validUntilRaw);

            if (!$validUntil instanceof \DateTimeImmutable || $validUntil->format('Y-m-d') !== $validUntilRaw) {
                return new JsonResponse(['error' => 'La date de validité est invalide.'], 400);
            }

            if ($validUntil <= new \DateTimeImmutable('today')) {
                return new JsonResponse(['error' => 'La date de validité doit être dans le futur.'], 400);
            }

            $this->validateCertificateService->updateStatus($certificate, $action, validUntil: $validUntil);

            return $this->json(['status' => $certificate->getStatus()]);
        }

        $reason = $request->request->get('reason');
        $reason = null !== $reason ? trim((string) $reason) : null;

        if (null === $reason || '' === $reason) {
            return new JsonResponse(['error' => 'Le motif du refus est requis.'], 400);
        }

        $this->validateCertificateService->updateStatus($certificate, $action, $reason);

        return $this->json(['status' => $certificate->getStatus()]);
    }
}
