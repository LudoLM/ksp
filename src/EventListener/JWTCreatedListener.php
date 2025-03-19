<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Bundle\SecurityBundle\Security;

class JWTCreatedListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        // Vérifiez que $event->getUser() est une instance de la classe utilisateur
        if (!$event->getUser() instanceof User) {
            throw new \Exception('Type de l\'utilisateur invalide');
        }

        $user = $event->getUser();

        // Exemple d'utilisation de $security
        if ($this->security->isGranted('ROLE_ADMIN')) {
            // Ajouter des informations spécifiques pour les administrateurs
            $payload = $event->getData();
            $payload['isAdmin'] = true;
            $event->setData($payload);
        }

        $payload = $event->getData();
        $payload['id'] = $user->getId();
        $payload['prenom'] = $user->getPrenom();

        $event->setData($payload);
    }
}
