<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * Vérifie l'état des utilisateurs lors de l'authentification.
 * Empêche les utilisateurs supprimés de se connecter.
 */
class UserChecker implements EventSubscriberInterface
{
    /**
     * @return array<string, array{0: string, 1: int}|array{0: string}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['checkUser', 256],
            AuthenticationSuccessEvent::class => ['onAuthenticationSuccess', -10],
        ];
    }

    public function checkUser(CheckPassportEvent $event): void
    {
        $user = $event->getPassport()->getUser();

        if (!$user instanceof User) {
            return;
        }

        // Bloquer la connexion si l'utilisateur est soft-deleted
        if ($user->isDeleted()) {
            throw new \Symfony\Component\Security\Core\Exception\DisabledException('Le compte utilisateur a été supprimé.');
        }

        // Bloquer la connexion si l'utilisateur est anonymisé
        if ($user->getAnonymisedAt() instanceof \DateTimeInterface) {
            throw new \Symfony\Component\Security\Core\Exception\DisabledException('Le compte utilisateur a été anonymisé.');
        }
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        // Rien à faire pour le moment, mais peut être utilisé pour d'autres vérifications
    }
}
