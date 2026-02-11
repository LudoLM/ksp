<?php

namespace App\Service\Notification;

use App\Service\Interface\Notification\NotificationInterface;
use App\Service\Interface\Notification\NotificationSenderInterface;
use App\Service\Interface\Notification\RecipientInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class NotificationManager
{
    /**
     * @param iterable<NotificationSenderInterface> $senders
     */
    public function __construct(
        #[AutowireIterator('app.notification_sender')]
        private iterable $senders,
    ) {
    }

    public function send(NotificationInterface $notification, RecipientInterface $recipient): void
    {
        $availableSenders = [];
        foreach ($this->senders as $sender) {
            $availableSenders[] = $sender::class;
            if ($sender->supports($notification)) {
                $sender->send($notification, $recipient);

                return;
            }
        }

        throw new \RuntimeException('Aucun expéditeur trouvé pour le type de notification : '.$notification->getType().'. Expéditeurs disponibles : '.implode(', ', $availableSenders));
    }
}
