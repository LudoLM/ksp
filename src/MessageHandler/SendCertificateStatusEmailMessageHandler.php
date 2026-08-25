<?php

namespace App\MessageHandler;

use App\Message\SendCertificateStatusEmailMessage;
use App\Repository\CertificatMedicalRepository;
use App\Service\SendingEmail\SendCertificateStatusEmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendCertificateStatusEmailMessageHandler
{
    public function __construct(
        private SendCertificateStatusEmailService $sendCertificateStatusEmailService,
        private CertificatMedicalRepository $certificatMedicalRepository,
    ) {
    }

    public function __invoke(SendCertificateStatusEmailMessage $message): void
    {
        $certificate = $this->certificatMedicalRepository->find($message->getCertificateId());

        if (null === $certificate) {
            throw new \Exception('Certificat non trouvé');
        }

        $this->sendCertificateStatusEmailService->send($certificate);
    }
}
