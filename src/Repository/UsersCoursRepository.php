<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UsersCours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UsersCours>
 */
class UsersCoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsersCours::class);
    }

    //    /**
    //     * @return UsersCours[] Returns an array of UsersCours objects
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

    //    public function findOneBySomeField($value): ?UsersCours
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getLastActivities(User $user)
    {
        return $this->createQueryBuilder('uc')
            ->where('uc.createdAt BETWEEN :lastVisit AND :now')
            ->orWhere('uc.unsubscribedAt BETWEEN :lastVisit AND :now')
            ->andWhere('uc.isOnWaitingList = :isOnWaitingList')
            ->setParameter('lastVisit', $user->getLastVisit())
            ->setParameter('now', new \DateTime())
            ->setParameter('isOnWaitingList', false)

            ->getQuery()
            ->getResult();
    }

    public function getLastActivitiesPerMonth(\DateTime $startDate, \DateTime $endDate, string $userName)
    {
        return $this->createQueryBuilder('uc')

            ->join('uc.user', 'u')
            ->where('uc.createdAt BETWEEN :start AND :end')
            ->orWhere('uc.unsubscribedAt BETWEEN :start AND :end')
            ->andWhere('u.nom LIKE :userName OR u.prenom LIKE :userName')
            ->andWhere('uc.isOnWaitingList = :isOnWaitingList')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('userName', '%'.$userName.'%')
            ->setParameter('isOnWaitingList', false)
            ->getQuery()
            ->getResult();
    }
}
