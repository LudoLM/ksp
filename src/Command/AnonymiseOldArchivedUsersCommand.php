<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\UserAnonymisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:anonymise-old-archived-users',
    description: 'Anonymise les utilisateurs archivés depuis plus d\'1 an',
)]
class AnonymiseOldArchivedUsersCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserAnonymisationService $anonymisationService,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $users = $this->userRepository->findOldArchivedUsers();

            if ([] === $users) {
                $io->info('Aucun utilisateur archivé à anonymiser.');

                return Command::SUCCESS;
            }

            $io->info('Anonymisation de '.count($users).' utilisateurs archivés depuis plus d\'1 an...');

            // Transaction wrapper
            $this->em->beginTransaction();

            $anonymisedCount = 0;
            foreach ($users as $user) {
                if ($this->anonymisationService->anonymiseUser($user, 'inactivity')) {
                    ++$anonymisedCount;
                }
            }

            $this->em->flush();

            $this->em->commit();

            $io->success($anonymisedCount.' utilisateurs ont été anonymisés avec succès.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->em->rollback();
            $io->error("Erreur lors de l'anonymisation: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
