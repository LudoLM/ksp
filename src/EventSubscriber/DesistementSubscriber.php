<?php

namespace App\EventSubscriber;

use App\Entity\UsersCours;
use App\Event\DesistementEvent;
use App\Message\SendDesistementEmailMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class DesistementSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function onDesistementEvent(DesistementEvent $event): void
    {
        $cours = $event->getCours();
        $usersCours = $cours->getUsersCours();
        $usersCours = array_filter($usersCours->toArray(), fn (UsersCours $usersCours): ?bool => $usersCours->isOnWaitingList());
        foreach ($usersCours as $user) {
            $this->messageBus->dispatch(new SendDesistementEmailMessage($cours->getId(), $user->getUser()->getId()));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DesistementEvent::class => 'onDesistementEvent',
        ];
    }
}
