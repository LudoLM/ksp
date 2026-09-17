<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\User;
use App\Enum\StatusCoursWishesFormEnum;
use App\Message\SendCoursWishesFormStatusEmailMessage;
use App\Service\Security\SecureTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ValidateCoursWishesFormService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $messageBus,
        private SecureTokenService $secureTokenService,
    ) {
    }

    public function updateStatus(CoursWishesForm $form, string $action, User $admin, ?string $correctionReason = null): void
    {
        $registrationToken = null;
        $correctionToken = null;

        if ('approve' === $action) {
            $form->setStatus(StatusCoursWishesFormEnum::VALIDE->value);
            $form->setCorrectionReason(null);

            if (!$form->getUser() instanceof User) {
                $registrationToken = $this->secureTokenService->generate();
                $form->setRegistrationTokenHash($this->secureTokenService->hash($registrationToken));
                $form->setRegistrationTokenExpiresAt(new \DateTimeImmutable('+30 days'));
            }
        } else {
            $form->setStatus(StatusCoursWishesFormEnum::A_CORRIGER->value);
            $form->setCorrectionReason($correctionReason);
            $correctionToken = $this->secureTokenService->generate();
            $form->setCorrectionTokenHash($this->secureTokenService->hash($correctionToken));
            $form->setCorrectionTokenExpiresAt(new \DateTimeImmutable('+30 days'));
        }

        $form->setReviewedBy($admin);
        $form->setReviewedAt(new \DateTimeImmutable());

        $this->em->flush();

        $this->messageBus->dispatch(new SendCoursWishesFormStatusEmailMessage($form->getId(), $registrationToken, $correctionToken));
    }
}
