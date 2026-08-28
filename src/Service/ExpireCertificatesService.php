<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\StatusCertificateEnum;
use App\Repository\CertificatMedicalRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

readonly class ExpireCertificatesService
{
    public function __construct(
        private CertificatMedicalRepository $repository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        #[Target('default.storage')] private FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @return array{expired: int}
     */
    public function expireCertificates(): array
    {
        $expirable = $this->repository->findExpirable();

        foreach ($expirable as $certificate) {
            $certificate->setStatus(StatusCertificateEnum::EXPIRED->value);
        }

        if ([] !== $expirable) {
            $this->em->flush();
            $this->logger->info(count($expirable).' certificat(s) médical(aux) expiré(s)');

            foreach ($expirable as $certificate) {
                $this->deleteFile($certificate->getCertificateFilename());
            }
        }

        return ['expired' => count($expirable)];
    }

    /**
     * Le fichier n'est supprimé du storage qu'une fois le changement de statut
     * validé en DB (voir expireCertificates). Une erreur de suppression ne doit
     * pas empêcher l'expiration des autres certificats de la même exécution.
     */
    private function deleteFile(string $filename): void
    {
        try {
            $this->filesystem->delete($filename);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur suppression fichier certificat expiré', [
                'exception' => $e,
                'filename' => $filename,
            ]);
        }
    }
}
