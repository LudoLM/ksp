<?php

namespace App\EventListener;

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Bundle\SecurityBundle\Security;

class JWTCreatedListener
{

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {

        $user = $event->getUser();
        $payload = $event->getData();
        $payload['id'] = $user->getId();
        $payload['prenom'] = $user->getPrenom();

        $event->setData($payload);
    }
}
