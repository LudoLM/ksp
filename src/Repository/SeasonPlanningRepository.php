<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SeasonPlanning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SeasonPlanning>
 */
class SeasonPlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeasonPlanning::class);
    }

    public function findBySaison(string $saison): ?SeasonPlanning
    {
        return $this->findOneBy(['saison' => $saison]);
    }
}
