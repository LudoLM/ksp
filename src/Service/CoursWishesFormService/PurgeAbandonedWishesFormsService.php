<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\Repository\CoursWishesFormRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

readonly class PurgeAbandonedWishesFormsService
{
    public function __construct(
        private CoursWishesFormRepository $repository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array{expiredApprovals: int, staleCorrections: int}
     */
    public function purge(): array
    {
        $expiredApprovals = $this->repository->findExpiredApprovals();
        $staleCorrections = $this->repository->findStaleCorrections();

        foreach ($expiredApprovals as $form) {
            $this->em->remove($form);
        }

        foreach ($staleCorrections as $form) {
            $this->em->remove($form);
        }

        if ([] !== $expiredApprovals || [] !== $staleCorrections) {
            $this->em->flush();
            $this->logger->info(\sprintf(
                '%d dossier(s) validé(s) avec lien expiré supprimé(s), %d dossier(s) en correction depuis plus de 30 jours supprimé(s)',
                \count($expiredApprovals),
                \count($staleCorrections),
            ));
        }

        return [
            'expiredApprovals' => \count($expiredApprovals),
            'staleCorrections' => \count($staleCorrections),
        ];
    }
}
