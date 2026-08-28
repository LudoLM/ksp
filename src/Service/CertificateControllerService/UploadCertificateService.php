<?php

declare(strict_types=1);

namespace App\Service\CertificateControllerService;

use App\Entity\CertificatMedical;
use App\Entity\User;
use App\Enum\StatusCertificateEnum;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

readonly class UploadCertificateService
{
    private const MAX_SIZE = 5 * 1024 * 1024;

    public function __construct(
        #[Target('default.storage')] private FilesystemOperator $filesystem,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    public function validate(UploadedFile $file): void
    {
        if ('application/pdf' !== $file->getMimeType() || 'pdf' !== strtolower($file->getClientOriginalExtension())) {
            throw new \InvalidArgumentException('Seuls les fichiers PDF sont acceptés.');
        }

        if ($file->getSize() > self::MAX_SIZE) {
            throw new \InvalidArgumentException('Le fichier ne doit pas dépasser 5 Mo.');
        }
    }

    public function upload(User $user, UploadedFile $file, ?User $uploadedBy = null): CertificatMedical
    {
        $newFilename = Uuid::v4().'.pdf';
        $existingCertificate = $this->findReplaceableCertificate($user);

        $this->em->beginTransaction();
        try {
            $this->filesystem->write($newFilename, $file->getContent());

            $oldFilePath = null;
            if ($existingCertificate instanceof CertificatMedical) {
                $oldFilePath = $existingCertificate->getCertificateFilename();
                $this->em->remove($existingCertificate);
                $this->em->flush();
            }

            $certificate = new CertificatMedical();
            $user->addCertificatMedical($certificate);
            $certificate->setCertificateFilename($newFilename);
            $certificate->setUploadedAt(new \DateTimeImmutable());
            $certificate->setStatus(StatusCertificateEnum::PENDING->value);
            $certificate->setUploadedBy($uploadedBy);

            $this->em->persist($certificate);
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            $this->filesystem->delete($newFilename);

            $this->logger->error('Erreur upload certificat', [
                'exception' => $e,
                'user' => $user->getUserIdentifier(),
            ]);

            throw $e;
        }

        if (null !== $oldFilePath) {
            $this->filesystem->delete($oldFilePath);
        }

        return $certificate;
    }

    /**
     * Un certificat approuvé reste actif tant qu'un remplaçant n'a pas lui-même
     * été approuvé (voir ValidateCertificateService) : seul un certificat en
     * attente ou refusé peut être remplacé par un nouvel upload.
     */
    private function findReplaceableCertificate(User $user): ?CertificatMedical
    {
        foreach ($user->getCertificatMedicaux() as $certificate) {
            if (StatusCertificateEnum::APPROVED->value !== $certificate->getStatus()) {
                return $certificate;
            }
        }

        return null;
    }
}
