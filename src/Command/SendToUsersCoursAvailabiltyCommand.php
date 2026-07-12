<?php

namespace App\Command;

use App\Message\SendToUsersCoursAvailabilityMessage;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:send-to-users-cours-availability',
    description: 'Envoie un mail aux utilisateurs pour les informer des places disponibles pour les cours de la semaine',
)]
class SendToUsersCoursAvailabiltyCommand extends Command
{
    public function __construct(
        private readonly CoursRepository $coursRepository,
        private readonly UserRepository $userRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            // Recuperer tous les users actifs
            $userIds = $this->userRepository->findIdsActiveUsers();
            // Recuperer tous les cours de la semaine prochaine qui ne sont pas remplis
            $coursIds = $this->coursRepository->findIdsOpenCoursForNextWeek();

            foreach ($userIds as $userId) {
                $this->messageBus->dispatch(new SendToUsersCoursAvailabilityMessage(
                    $userId,
                    $coursIds
                ));
            }
            // Envoyer les messages.
            $io->success('Mails envoyés aux utilisateurs pour les informer des places disponibles');
        } catch (\Exception $e) {
            $io->error("Erreur lors de l'envoi aux utilisateurs: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
