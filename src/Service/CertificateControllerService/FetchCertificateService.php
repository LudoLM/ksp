<?php

declare(strict_types=1);

namespace App\Service\CertificateControllerService;

use App\Entity\CertificatMedical;
use App\Enum\StatusCertificateEnum;
use App\Repository\CertificatMedicalRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Target;

readonly class FetchCertificateService
{
    public function __construct(
        #[Target('default.storage')] private FilesystemOperator $filesystem,
        private CertificatMedicalRepository $repository,
    ) {
    }

    public function getPendingCertificates(int $page, int $limit): array
    {
        $paginator = $this->repository->paginatePending($page, $limit);
        $totalItems = $paginator->count();

        $data = [];
        foreach ($paginator as $certificate) {
            $user = $certificate->getUser();
            $data[] = [
                'id' => $certificate->getId(),
                'status' => $certificate->getStatus(),
                'uploadedAt' => $certificate->getUploadedAt()->format('Y-m-d H:i:s'),
                'validUntil' => $certificate->getValidUntil()?->format('Y-m-d'),
                'certificateFilename' => $certificate->getCertificateFilename(),
                'user' => [
                    'id' => $user?->getId(),
                    'nom' => $user?->getNom(),
                    'prenom' => $user?->getPrenom(),
                    'email' => $user?->getEmail(),
                ],
            ];
        }

        return [
            'metadata' => [
                'total_items' => $totalItems,
                'current_page' => $page,
                'total_pages' => (int) ceil($totalItems / $limit),
            ],
            'data' => $data,
        ];
    }

    public function readCertificateContent(CertificatMedical $certificate): string
    {
        $filename = $certificate->getCertificateFilename();

        if (!$this->filesystem->fileExists($filename)) {
            throw new \RuntimeException('Fichier introuvable.');
        }

        return $this->filesystem->read($filename);
    }

    /**
     * Détermine, parmi les certificats d'un utilisateur, celui à lui afficher :
     * celui en attente de traitement en priorité (une review est en cours),
     * sinon le dernier approuvé, sinon le dernier refusé.
     *
     * Statique : ne dépend d'aucun service injecté, ce qui permet de l'appeler
     * depuis App\Entity\User (qui ne peut pas recevoir d'injection de dépendances)
     * sans dupliquer la logique de sélection.
     *
     * @param iterable<CertificatMedical> $certificates
     */
    public static function selectCurrentCertificate(iterable $certificates): ?CertificatMedical
    {
        $pending = null;
        $approved = null;
        $rejected = null;

        foreach ($certificates as $certificate) {
            match ($certificate->getStatus()) {
                StatusCertificateEnum::PENDING->value => $pending ??= $certificate,
                StatusCertificateEnum::APPROVED->value => $approved ??= $certificate,
                StatusCertificateEnum::REJECTED->value => $rejected ??= $certificate,
                default => null,
            };
        }

        return $pending ?? $approved ?? $rejected;
    }
}
