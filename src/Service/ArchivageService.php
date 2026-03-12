<?php

declare(strict_types=1);

namespace App\Service;

use App\Constant\ArchivageConstants;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

readonly class ArchivageService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Archive les utilisateurs inactifs selon les critères métier
     * Critères: nombreCours <= 0 ET last_visit < MONTHS_INACTIVE_THRESHOLD mois ET isArchived = false.
     *
     * @param int $monthsInactive Nombre de mois d'inactivité avant archivage (défaut: MONTHS_INACTIVE_THRESHOLD)
     *
     * @return array ['archived' => int, 'errors' => string[]]
     */
    public function archiveInactiveUsers(int $monthsInactive = ArchivageConstants::MONTHS_INACTIVE_THRESHOLD): array
    {
        $inactiveUsers = $this->userRepository->findInactiveUsers($monthsInactive);
        $archivedCount = 0;
        $errors = [];

        foreach ($inactiveUsers as $user) {
            try {
                $this->archiveUser($user, $monthsInactive);
                ++$archivedCount;
            } catch (\Exception $e) {
                $errorMsg = "Erreur archivage {$user->getId()}: ".$e->getMessage();
                $errors[] = $errorMsg;
                $this->logger->error('Archivage user failed', [
                    'userId' => $user->getId(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($archivedCount > 0) {
            $this->em->flush();
            $this->logger->info("{$archivedCount} utilisateurs archivés");
        }

        return ['archived' => $archivedCount, 'errors' => $errors];
    }

    /**
     * Archive un utilisateur spécifique.
     *
     * @throws \InvalidArgumentException Si l'utilisateur ne remplit pas les critères d'archivage
     */
    public function archiveUser(User $user, int $monthsInactive = ArchivageConstants::MONTHS_INACTIVE_THRESHOLD): void
    {
        $this->validateCanArchive($user, $monthsInactive);

        $user->setIsArchived(true);
        $user->setArchivedAt(new \DateTime());

        $this->em->persist($user);

        $this->logger->info('Utilisateur archivé', [
            'userId' => $user->getId(),
        ]);
    }

    /**
     * Désarchive un utilisateur.
     * Si $adminUser est null, c'est un désarchivage automatique.
     *
     * @param User $user Utilisateur à désarchiver
     */
    public function unarchiveUser(User $user): void
    {
        if (!$user->isArchived()) {
            return;
        }

        $user->setIsArchived(false);
        $user->setArchivedAt(null);

        $this->em->persist($user);

        $this->logger->info('Utilisateur désarchivé', [
            'userId' => $user->getId(),
        ]);
    }

    /**
     * Valide que l'utilisateur peut être archivé.
     *
     * @throws \InvalidArgumentException Si l'utilisateur ne remplit pas les critères
     */
    private function validateCanArchive(User $user, int $monthsInactive): void
    {
        if (($user->getNombreCours() ?? 0) > 0) {
            throw new \InvalidArgumentException("Impossible d'archiver: {$user->getNombreCours()} cours restants");
        }

        $date = new \DateTime('-'.$monthsInactive.' months');
        if ($user->getLastVisit() instanceof \DateTimeImmutable && $user->getLastVisit() > $date) {
            throw new \InvalidArgumentException('Impossible d\'archiver: dernière visite récente');
        }
    }
}
