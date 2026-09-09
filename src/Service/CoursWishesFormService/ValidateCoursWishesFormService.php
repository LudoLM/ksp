<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\User;
use App\Enum\StatusCoursWishesFormEnum;
use App\Message\SendCoursWishesFormStatusEmailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ValidateCoursWishesFormService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function updateStatus(CoursWishesForm $form, string $action, User $admin, ?string $correctionReason = null): void
    {
        if ('approve' === $action) {
            $form->setStatus(StatusCoursWishesFormEnum::VALIDE->value);
            $form->setCorrectionReason(null);
        } else {
            $form->setStatus(StatusCoursWishesFormEnum::A_CORRIGER->value);
            $form->setCorrectionReason($correctionReason);
        }

        $form->setValidatedBy($admin);
        $form->setValidatedAt(new \DateTimeImmutable());

        $this->em->flush();

        $this->messageBus->dispatch(new SendCoursWishesFormStatusEmailMessage($form->getId()));
    }
}
