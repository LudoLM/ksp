<?php

namespace App\Repository;

use App\Entity\TypeCours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TypeCoursRepository extends ServiceEntityRepository
{
    /**
     * @extends ServiceEntityRepository<TypeCours>
     *
     * @method TypeCours|null find($id, $lockMode = null, $lockVersion = null)
     * @method TypeCours|null findOneBy(array $criteria, array $orderBy = null)
     * @method TypeCours[]    findAll()
     * @method TypeCours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
     */
    public function __construct(
        private readonly ManagerRegistry $registry,
    )
    {
        parent::__construct($registry, TypeCours::class);
    }
}
