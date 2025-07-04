<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\TypeCours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cours>
 *
 * @method Cours|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cours|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cours[]    findAll()
 * @method Cours[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cours::class);
    }

    public function save(Cours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cours $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Cours[] Returns an array of Cours objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cours
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findAllSortByDate(
        ?TypeCours $typeCours,
        ?\DateTime $dateCours,
        ?\DateTime $dateLimit,
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.dateCours', 'ASC');

        // Ajouter les filtres dynamiques
        if ($typeCours instanceof TypeCours) {
            $qb->andWhere('c.typeCours = :typeCours')
                ->setParameter('typeCours', $typeCours);
        }

        if ($dateCours instanceof \DateTime) {
            $qb->andWhere('c.dateCours >= :dateCours')
                ->setParameter('dateCours', $dateCours);
        }

        if ($dateLimit instanceof \DateTime) {
            $qb->andWhere('c.dateCours <= :dateLimit')
                ->setParameter('dateLimit', $dateLimit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllSortByDateForUsers(
        ?TypeCours $typeCours,
        ?\DateTime $dateCours,
        ?\DateTime $dateLimit,
        bool $isPrioritized,
    ): array {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.dateCours', 'ASC')
            ->where('c.statusCours IN (1, 2, 3, 5)');

        // Ajouter les filtres dynamiques
        if ($typeCours instanceof TypeCours) {
            $qb->andWhere('c.typeCours = :typeCours')
                ->setParameter('typeCours', $typeCours);
        }

        if ($dateCours instanceof \DateTime) {
            $qb->andWhere('c.dateCours > :dateCours')
                ->setParameter('dateCours', $dateCours);
        }

        if ($dateLimit instanceof \DateTime) {
            $qb->andWhere('c.dateCours <= :dateLimit')
                ->setParameter('dateLimit', $dateLimit);
        }

        if (!$isPrioritized) {
            $qb->andWhere('c.hasPriority = false OR c.launchedAt < :launchedAt')
                ->setParameter('launchedAt', (new \DateTime())->modify('-1 week'));
        }

        return $qb->getQuery()->getResult();
    }

    public function findNextCours(
        ?TypeCours $typeCours,
        ?\DateTime $dateCours,
        bool $isPrioritized,
    ): ?Cours {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.dateCours', 'ASC')
            ->where('c.statusCours IN (1)');

        if ($typeCours instanceof TypeCours) {
            $qb->andWhere('c.typeCours = :typeCours')
                ->setParameter('typeCours', $typeCours);
        }

        if ($dateCours instanceof \DateTime) {
            $qb->andWhere('c.dateCours > :dateCours')
                ->setParameter('dateCours', $dateCours);
        }

        // s'il l'utilisateur n'est pas isPrioritized alors il faut que le cours a été créé il y a + d'une semaine ou qu'il soit à hasPriority false
        if (!$isPrioritized) {
            $qb->andWhere('c.hasPriority = false OR c.launchedAt < :launchedAt')
                ->setParameter('launchedAt', (new \DateTime())->modify('-1 week'));
        }

        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getCoursFilling(): array
    {
        return $this->createQueryBuilder('c')
            ->select('NEW App\DTO\CoursFillingDTO(
            c.id,
            c.dateCours,
            c.nbInscriptionMax,
            COUNT(u.id)
        )')
            ->leftJoin('c.usersCours', 'u')
            ->where('c.statusCours = 1 OR c.statusCours = 2')
            ->andWhere('c.dateCours > :currentDate')
            ->andWhere('c.dateCours < :dateLimit')
            ->setParameter('currentDate', new \DateTime())
            ->setParameter('dateLimit', (new \DateTime())->modify('+90days'))
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();
    }

    public function getSuperLightAllCours(): array
    {
        return $this->createQueryBuilder('c')
            ->select('NEW App\DTO\SuperLightCoursDTO(
            c.id,
            c.dateCours,
            c.duree,
            statusCours.id
        )')
            ->join('c.statusCours', 'statusCours')
            ->where('statusCours.id != :archived')
            ->setParameter('archived', 7)
            ->getQuery()
            ->getResult();
    }

    public function getYearsRangeForCours(): array
    {
        $result = $this->createQueryBuilder('c')
            ->select('MIN(c.dateCours) as min', 'MAX(c.dateCours) as max')
            ->getQuery()
            ->getSingleResult();

        return [
            'min' => (new \DateTime($result['min']))->format('Y'),
            'max' => (new \DateTime($result['max']))->format('Y'),
        ];
    }
}
