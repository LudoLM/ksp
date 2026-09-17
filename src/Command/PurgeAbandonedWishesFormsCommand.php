<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\CoursWishesForm;
use App\Repository\CoursWishesFormRepository;
use App\Service\CoursWishesFormService\PurgeAbandonedWishesFormsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:purge-abandoned-wishes-forms',
    description: 'Supprime les dossiers d\'inscription validés dont le lien d\'inscription a expiré, et les dossiers en attente de correction depuis plus de 30 jours',
)]
class PurgeAbandonedWishesFormsCommand extends Command
{
    public function __construct(
        private readonly PurgeAbandonedWishesFormsService $purgeService,
        private readonly CoursWishesFormRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Affiche les dossiers qui seraient supprimés sans modifier la base de données'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        if ($dryRun) {
            $io->section('Mode DRY-RUN - Dossiers validés avec lien d\'inscription expiré :');
            $this->displayTable($io, $this->repository->findExpiredApprovals());

            $io->section('Mode DRY-RUN - Dossiers en correction depuis plus de 30 jours :');
            $this->displayTable($io, $this->repository->findStaleCorrections());

            return Command::SUCCESS;
        }

        $io->section('Purge des dossiers d\'inscription abandonnés...');

        try {
            $this->em->beginTransaction();

            $result = $this->purgeService->purge();

            $this->em->commit();

            $io->success(\sprintf(
                '✓ %d dossier(s) validé(s) expiré(s) supprimé(s), %d dossier(s) en correction supprimé(s)',
                $result['expiredApprovals'],
                $result['staleCorrections'],
            ));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->em->rollback();
            $io->error("Erreur lors de la purge des dossiers d'inscription: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    /**
     * @param CoursWishesForm[] $forms
     */
    private function displayTable(SymfonyStyle $io, array $forms): void
    {
        if ([] === $forms) {
            $io->info('Aucun dossier.');

            return;
        }

        $io->table(
            ['ID', 'Email', 'Saison', 'Statut'],
            array_map(static fn (CoursWishesForm $form): array => [
                $form->getId(),
                $form->getEmail(),
                $form->getSaison(),
                $form->getStatus(),
            ], $forms)
        );

        $io->note('Total: '.\count($forms).' dossier(s)');
    }
}
