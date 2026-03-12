<?php

declare(strict_types=1);

namespace App\Command;

use App\Constant\ArchivageConstants;
use App\Repository\UserRepository;
use App\Service\ArchivageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:archive-inactive-users',
    description: 'Archive les utilisateurs inactifs ('.ArchivageConstants::MONTHS_INACTIVE_THRESHOLD.' mois sans accès et 0 cours restants)',
)]
class ArchiveInactiveUsersCommand extends Command
{
    public function __construct(
        private readonly ArchivageService $archivageService,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'months',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Nombre de mois d\'inactivité requis pour archivage (défaut: '.ArchivageConstants::MONTHS_INACTIVE_THRESHOLD.')',
                ArchivageConstants::MONTHS_INACTIVE_THRESHOLD
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Affiche ce qui serait archivé sans modifier la base de données'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $months = (int) $input->getOption('months');
        $dryRun = $input->getOption('dry-run');

        $io->info("Recherche des utilisateurs inactifs depuis {$months} mois...");

        // Mode dry-run : afficher seulement
        if ((bool) $dryRun) {
            $inactiveUsers = $this->userRepository->findInactiveUsers($months);
            $io->section('Mode DRY-RUN - Les utilisateurs suivants seraient archivés:');

            if ([] === $inactiveUsers) {
                $io->info('Aucun utilisateur inactif trouvé');

                return Command::SUCCESS;
            }

            $io->table(
                ['ID', 'Email', 'Prénom', 'Nom', 'Cours restants', 'Dernière visite'],
                array_map(fn ($user): array => [
                    $user->getId(),
                    $user->getEmail(),
                    $user->getPrenom(),
                    $user->getNom(),
                    $user->getNombreCours() ?? 0,
                    $user->getLastVisit()?->format('Y-m-d H:i:s') ?? 'Jamais',
                ], $inactiveUsers)
            );

            $io->note('Total: '.count($inactiveUsers).' utilisateurs');

            return Command::SUCCESS;
        }

        // Archivage réel
        $io->section('Archivage des utilisateurs inactifs...');

        try {
            // Transaction wrapper
            $this->em->beginTransaction();

            $result = $this->archivageService->archiveInactiveUsers($months);

            $this->em->commit();

            $io->success("✓ {$result['archived']} utilisateurs ont été archivés");

            if (count($result['errors']) > 0) {
                $io->warning('Erreurs rencontrées:');
                foreach ($result['errors'] as $error) {
                    $output->writeln("  • {$error}");
                }

                return Command::FAILURE;
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->em->rollback();
            $io->error("Erreur lors de l'archivage: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
