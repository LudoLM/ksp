<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SeasonPlanning;
use App\Entity\SeasonPlanningSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SeasonPlanningSlot>
 */
class SeasonPlanningSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeasonPlanningSlot::class);
    }

    /**
     * @return SeasonPlanningSlot[]
     */
    public function findAllForSeasonPlanning(SeasonPlanning $planning): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.seasonPlanning = :planning')
            ->leftJoin('s.typeCoursOptions', 't')
            ->addSelect('t')
            ->setParameter('planning', $planning)
            ->orderBy('s.daySelected', 'ASC')
            ->addOrderBy('s.timeSelected', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Le créneau (jour/heure) lui-même, indépendamment des cours qui y sont
     * associés — un créneau est unique par (planning, jour, heure).
     */
    public function findByDayTime(SeasonPlanning $planning, int $daySelected, \DateTimeInterface $timeSelected): ?SeasonPlanningSlot
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.seasonPlanning = :planning')
            ->andWhere('s.daySelected = :day')
            ->andWhere('s.timeSelected = :time')
            ->setParameter('planning', $planning)
            ->setParameter('day', $daySelected)
            ->setParameter('time', $timeSelected)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
