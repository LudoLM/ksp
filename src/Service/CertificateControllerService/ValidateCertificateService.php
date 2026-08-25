<?php

declare(strict_types=1);

namespace App\Service\CertificateControllerService;

use App\Entity\CertificatMedical;
use App\Enum\StatusCertificateEnum;
use App\Message\SendCertificateStatusEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ValidateCertificateService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function updateStatus(CertificatMedical $certificate, string $action, ?string $reason = null): void
    {
        if ('approve' === $action) {
            $certificate->setStatus(StatusCertificateEnum::APPROVED->value);
            $certificate->setValidUntil(new \DateTimeImmutable()->modify('+1 year'));
            $certificate->setRejectionReason(null);
        } else {
            $certificate->setStatus(StatusCertificateEnum::REJECTED->value);
            $certificate->setRejectionReason($reason);
        }

        $this->em->flush();

        $this->messageBus->dispatch(new SendCertificateStatusEmailMessage($certificate->getId()));
    }
}
