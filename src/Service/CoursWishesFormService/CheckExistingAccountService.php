<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\Entity\User;
use App\Message\SendExistingAccountEmailMessage;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class CheckExistingAccountService
{
    public function __construct(
        private UserRepository $userRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * Pour une demande d'inscription : si un compte existe déjà pour cet email,
     * notifie son propriétaire (au lieu de créer un dossier) et retourne true.
     * Ne révèle jamais côté appelant si un compte a été trouvé ou non (RGPD,
     * anti-énumération) — seule la décision "faut-il court-circuiter ?" sort d'ici.
     */
    public function handleIfExists(string $email): bool
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);

        if (!$existingUser instanceof User) {
            return false;
        }

        $this->messageBus->dispatch(new SendExistingAccountEmailMessage($existingUser->getId()));

        return true;
    }
}
