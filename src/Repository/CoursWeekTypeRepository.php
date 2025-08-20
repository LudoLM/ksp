<?php

namespace App\Repository;

use App\Entity\CoursWeekType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoursWeekType>
 *
 * @method CoursWeekType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoursWeekType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoursWeekType[]    findAll()
 * @method CoursWeekType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursWeekTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoursWeekType::class);
    }

    public function save(CoursWeekType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CoursWeekType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
