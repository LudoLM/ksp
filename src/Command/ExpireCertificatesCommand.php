<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\CertificatMedicalRepository;
use App\Service\ExpireCertificatesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:expire-certificates',
    description: 'Passe au statut "Expired" les certificats médicaux approuvés dont la date de validité est dépassée',
)]
class ExpireCertificatesCommand extends Command
{
    public function __construct(
        private readonly ExpireCertificatesService $expireCertificatesService,
        private readonly CertificatMedicalRepository $repository,
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
            'Affiche les certificats qui seraient expirés sans modifier la base de données'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');

        if ($dryRun) {
            $expirable = $this->repository->findExpirable();
            $io->section('Mode DRY-RUN - Les certificats suivants seraient expirés :');

            if ([] === $expirable) {
                $io->info('Aucun certificat à expirer.');

                return Command::SUCCESS;
            }

            $io->table(
                ['ID', 'Utilisateur', 'Valide jusqu\'au'],
                array_map(fn ($certificate): array => [
                    $certificate->getId(),
                    $certificate->getUser()?->getEmail(),
                    $certificate->getValidUntil()?->format('Y-m-d'),
                ], $expirable)
            );

            $io->note('Total: '.count($expirable).' certificat(s)');

            return Command::SUCCESS;
        }

        $io->section('Expiration des certificats médicaux...');

        try {
            $this->em->beginTransaction();

            $result = $this->expireCertificatesService->expireCertificates();

            $this->em->commit();

            $io->success("✓ {$result['expired']} certificat(s) expiré(s)");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->em->rollback();
            $io->error("Erreur lors de l'expiration des certificats: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
