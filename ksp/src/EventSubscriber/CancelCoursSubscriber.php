<?php

namespace App\EventSubscriber;

use App\Enum\StatusCoursEnum;
use App\Event\CancelCoursEvent;
use App\Repository\StatusCoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class CancelCoursSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerInterface $mailer, private Security $security, private EntityManagerInterface $em, private StatusCoursRepository $statusCoursRepository)
    {
    }
    public function onCoursCancel(CancelCoursEvent $event): void
    {;
        $cours = $event->getCours();
        $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ANNULE->value]));

        $this->em->persist($cours);
        foreach ($cours->getUsersCours() as $participant){
            $email = (new TemplatedEmail())
                ->from('test@test.fr')
                ->to('test@test.fr')
                ->subject('Annulation du cours')
                ->htmlTemplate('emails/cancel.html.twig')
                ->locale('fr')
                ->context([
                    'cours' => $cours,
                    'participant' => $participant->getUser(),
                    'user' => $this->security->getUser()
                ]);
            $this->mailer->send($email);
            $participant->getUser()->setNombreCours($participant->getUser()->getNombreCours() + 1);
            $this->em->persist($participant->getUser());
        }

        $this->em->flush();

    }



    public static function getSubscribedEvents(): array
    {
        return [
            CancelCoursEvent::class => 'onCoursCancel',
        ];
    }
}
