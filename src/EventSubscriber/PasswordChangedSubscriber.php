<?php

namespace App\EventSubscriber;

use App\Event\PasswordChangedEvent;
use App\Message\SendPasswordChangedNotificationMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class PasswordChangedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function onPasswordChanged(PasswordChangedEvent $event): void
    {
        $user = $event->getUser();
        $this->messageBus->dispatch(new SendPasswordChangedNotificationMessage($user->getId()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PasswordChangedEvent::class => 'onPasswordChanged',
        ];
    }
}
