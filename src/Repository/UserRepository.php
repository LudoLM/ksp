<?php

namespace App\Repository;

use App\Constant\ArchivageConstants;
use App\DTO\UserContactDTO;
use App\Entity\Cours;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function getLightUsersAll(Cours $cours): array
    {
        // Récupérer les IDs des utilisateurs déjà inscrits dans le cours
        $usersCours = $cours->getUsersCours()
            ->map(fn ($userCours): ?int => $userCours->getUser()->getId())
            ->toArray();
        $usersCours[] = 1; // Ajouter l'ID de l'admin pour ne pas l'afficher

        return $this->createQueryBuilder('u')
            ->select('NEW App\DTO\LightUserDTO(u.id, u.prenom, u.nom)')
            ->andWhere('u.id NOT IN (:usersCours)')
            ->andWhere('u.anonymisedAt IS NULL')
            ->setParameter('usersCours', $usersCours)
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getAvailableUsers(Cours $cours, string $search): array
    {
        return $this->createQueryBuilder('u')
            ->select('NEW App\DTO\LightUserDTO(u.id, u.prenom, u.nom)')
            ->leftJoin('u.usersCours', 'uc', 'WITH', 'uc.cours = :cours')
            ->andWhere(
                // Soit pas d'inscription du tout
                // Soit désinscrit (unsubscribedAt non null) ET la désinscription est après la dernière inscription
                'uc.id IS NULL OR (uc.unsubscribedAt IS NOT NULL AND uc.unsubscribedAt > uc.createdAt)'
            )
            ->andWhere('u.anonymisedAt IS NULL')
            ->andWhere('u.nom LIKE :search OR u.prenom LIKE :search')
            ->setParameter('cours', $cours)
            ->setParameter('search', '%'.$search.'%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function paginateUsers(
        int $page = 1,
        int $limit = 10,
        string $searchUser = '',
        bool $excludeArchived = true,
    ): Paginator {
        $query = $this->createQueryBuilder('u')
            ->where('(u.nom LIKE :searchUser OR u.prenom LIKE :searchUser OR u.email LIKE :searchUser)')
            ->setParameter('searchUser', '%'.$searchUser.'%');

        if ($excludeArchived) {
            $query->andWhere('u.isArchived = false');
        }
        $query->andWhere('u.anonymisedAt IS NULL')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->orderBy('u.nom', 'ASC');

        return new Paginator($query->getQuery());
    }

    public function resetAllUsersCounterCours()
    {
        $qb = $this->createQueryBuilder('u')
            ->update()
            ->set('u.nombreCours', 0)
            ->set('u.isPrioritized', 0)
            ->where('u.roles NOT LIKE :adminRole')
            ->setParameter('adminRole', '%"ROLE_ADMIN"%');

        return $qb->getQuery()->execute();
    }

    /**
     * Trouve les utilisateurs inactifs et archivables
     * Critères: nombreCours <= 0 AND last_visit < X mois AND isArchived = false.
     *
     * @param int $monthsInactive Nombre de mois d'inactivité avant archivage
     *
     * @return User[]
     */
    public function findInactiveUsers(int $monthsInactive = ArchivageConstants::MONTHS_INACTIVE_THRESHOLD): array
    {
        $date = new \DateTime("-{$monthsInactive} months");

        return $this->createQueryBuilder('u')
            ->where('u.nombreCours <= :zeroCours')
            ->andWhere('(u.last_visit IS NULL OR u.last_visit < :date)')
            ->andWhere('u.isArchived = false')
            ->andWhere('u.isDeleted = false')
            ->andWhere('u.anonymisedAt IS NULL')
            ->setParameter('zeroCours', 0)
            ->setParameter('date', $date)
            ->orderBy('u.last_visit', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs archivés depuis plus de ANONYMISATION_DELAY_MONTHS mois à anonymiser.
     * Optimized: All filtering done in SQL instead of PHP memory.
     *
     * @return User[]
     *
     * @throws \DateMalformedStringException
     */
    public function findOldArchivedUsers(): array
    {
        $anonymisationThreshold = new \DateTime('-'.ArchivageConstants::ANONYMISATION_DELAY_MONTHS.' months');

        return $this->createQueryBuilder('u')
            ->where('u.isArchived = true')
            ->andWhere('u.isDeleted = false')
            ->andWhere('u.archivedAt IS NOT NULL')
            ->andWhere('u.archivedAt < :threshold')
            ->andWhere('u.anonymisedAt IS NULL')
            ->setParameter('threshold', $anonymisationThreshold)
            ->orderBy('u.archivedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findIdsActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.id')
            ->where('u.isArchived = false')
            ->andWhere('u.isDeleted = false')
            ->andWhere('u.anonymisedAt IS NULL')
            ->andWhere('u.nombreCours > 0')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findUserContact(int $id): ?UserContactDTO
    {
        return $this->createQueryBuilder('u')
            ->select('NEW App\DTO\UserContactDTO(
                u.id,
                u.prenom,
                u.nom,
                u.email,
                u.telephone
            )')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //    /**
    //     * @return user[] Returns an array of user objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?user
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
