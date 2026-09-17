<?php

namespace App\MessageHandler;

use App\Message\SendCoursWishesFormStatusEmailMessage;
use App\Repository\CoursWishesFormRepository;
use App\Service\SendingEmail\SendCoursWishesFormStatusEmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendCoursWishesFormStatusEmailMessageHandler
{
    public function __construct(
        private SendCoursWishesFormStatusEmailService $sendCoursWishesFormStatusEmailService,
        private CoursWishesFormRepository $coursWishesFormRepository,
    ) {
    }

    public function __invoke(SendCoursWishesFormStatusEmailMessage $message): void
    {
        $form = $this->coursWishesFormRepository->find($message->getFormId());

        if (null === $form) {
            throw new \Exception('Dossier d\'inscription non trouvé');
        }

        $this->sendCoursWishesFormStatusEmailService->send($form, $message->getRegistrationToken(), $message->getCorrectionToken());
    }
}
