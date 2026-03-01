<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UsersCours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
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
        $qb = $this->createQueryBuilder('uc');
        $dateWindow = new Orx([
            'uc.createdAt BETWEEN :lastVisit AND :now',
            'uc.unsubscribedAt BETWEEN :lastVisit AND :now',
        ]);

        return $qb
            ->andWhere($dateWindow)
            ->andWhere('uc.isOnWaitingList = :isOnWaitingList')
            ->setParameter('lastVisit', $user->getLastVisit())
            ->setParameter('now', new \DateTime())
            ->setParameter('isOnWaitingList', false)

            ->getQuery()
            ->getResult();
    }

    public function getLastActivitiesPerMonth(\DateTime $startDate, \DateTime $endDate, string $userName)
    {
        $qb = $this->createQueryBuilder('uc');
        $dateWindow = new Orx([
            'uc.createdAt BETWEEN :start AND :end',
            'uc.unsubscribedAt BETWEEN :start AND :end',
        ]);

        return $qb
            ->join('uc.user', 'u')
            ->andWhere($dateWindow)
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
