<?php

namespace App\EventSubscriber;

use App\Entity\UsersCours;
use App\Event\DesistementEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

readonly class DesistementSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerInterface $mailer, private Security $security)
    {
    }

    public function onDesistementEvent(DesistementEvent $event): void
    {
        $cours = $event->getCours();
        $usersCours = $cours->getUsersCours();
        $usersCours = array_filter($usersCours->toArray(), fn (UsersCours $usersCours): ?bool => $usersCours->isOnWaitingList());
        foreach ($usersCours as $user) {
            $email = new TemplatedEmail()
                ->from('test@test.fr')
                ->to($user->getUser()->getEmail())
                ->subject('Place disponible pour le cours')
                ->htmlTemplate('emails/attente.html.twig')
                ->context([
                    'cours' => $cours,
                    'participant' => $user->getUser(),
                    'user' => $this->security->getUser(),
                ]);

            $this->mailer->send($email);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DesistementEvent::class => 'onDesistementEvent',
        ];
    }
}
