<?php

declare(strict_types=1);

namespace App\Service\CertificateControllerService;

use App\Entity\CertificatMedical;
use App\Entity\User;
use App\Enum\StatusCertificateEnum;
use App\Message\SendCertificateStatusEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ValidateCertificateService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $messageBus,
        #[Target('default.storage')] private FilesystemOperator $filesystem,
    ) {
    }

    public function updateStatus(CertificatMedical $certificate, string $action, ?string $reason = null, ?\DateTimeImmutable $validUntil = null): void
    {
        $filesToDelete = [];

        $this->em->beginTransaction();
        try {
            if ('approve' === $action) {
                $filesToDelete = $this->removePreviousApprovedCertificate($certificate);
                $certificate->setStatus(StatusCertificateEnum::APPROVED->value);
                $certificate->setValidUntil($validUntil);
                $certificate->setRejectionReason(null);
            } else {
                $certificate->setStatus(StatusCertificateEnum::REJECTED->value);
                $certificate->setRejectionReason($reason);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();

            throw $e;
        }

        foreach ($filesToDelete as $filename) {
            $this->filesystem->delete($filename);
        }

        $this->messageBus->dispatch(new SendCertificateStatusEmailMessage($certificate->getId()));
    }

    /**
     * Un certificat approuvé n'est remplacé qu'au moment où son remplaçant
     * est lui-même approuvé, pas dès l'upload : ça évite qu'un utilisateur
     * se retrouve sans certificat valide pendant la review du nouveau.
     *
     * Les fichiers ne sont supprimés du storage qu'après le commit de la
     * transaction DB (voir updateStatus), pour éviter de perdre un fichier
     * si le flush échoue.
     *
     * @return string[] noms des fichiers à supprimer une fois la transaction validée
     */
    private function removePreviousApprovedCertificate(CertificatMedical $newCertificate): array
    {
        $user = $newCertificate->getUser();
        if (!$user instanceof User) {
            return [];
        }

        $filesToDelete = [];
        foreach ($user->getCertificatMedicaux() as $existing) {
            if ($existing !== $newCertificate && StatusCertificateEnum::APPROVED->value === $existing->getStatus()) {
                $filesToDelete[] = $existing->getCertificateFilename();
                $this->em->remove($existing);
            }
        }

        return $filesToDelete;
    }
}
