<?php

namespace App\Repository;

use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Entity\TypeCours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

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

    public function findCoursFiltered() : array
    {
        $qb = $this->createQueryBuilder('c');
        return $qb->getQuery()->getResult();
    }

    public function findAllSortByDate(
        int $currentPage,
        int $maxPerPage,
        ?TypeCours $typeCours,
        ?\DateTime $dateCours,
        ?StatusCours $statusCours
    ): Paginator {


        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.dateCours', 'DESC');

        // Ajouter les filtres dynamiques
        if ($typeCours !== null) {
            $qb->andWhere('c.typeCours = :typeCours')
                ->setParameter('typeCours', $typeCours);
        }

        if ($dateCours !== null) {
            $qb->andWhere('c.dateCours > :dateCours')
                ->setParameter('dateCours', $dateCours);
        }

        if ($statusCours !== null) {
            $qb->andWhere('c.statusCours = :statusCours')
                ->setParameter('statusCours', $statusCours);
        }

        // Ajouter la pagination
        $qb->setFirstResult(($currentPage - 1) * $maxPerPage)
            ->setMaxResults($maxPerPage);

        // Retourner un Paginator
        return new Paginator($qb->getQuery());
    }


    public function findAllSortByDateForUsers(
        int $currentPage,
        int $maxPerPage,
        ?TypeCours $typeCours,
        ?\DateTime $dateCours,
        ?StatusCours $statusCours
    ): Paginator {


        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.dateCours', 'DESC')
            ->where('c.statusCours = 1 OR c.statusCours = 2 OR c.statusCours = 3 OR c.statusCours = 5');

        // Ajouter les filtres dynamiques
        if ($typeCours !== null) {
            $qb->andWhere('c.typeCours = :typeCours')
                ->setParameter('typeCours', $typeCours);
        }

        if ($dateCours !== null) {
            $qb->andWhere('c.dateCours > :dateCours')
                ->setParameter('dateCours', $dateCours);
        }

        if ($statusCours !== null) {
            $qb->andWhere('c.statusCours = :statusCours')
                ->setParameter('statusCours', $statusCours);
        }

        // Ajouter la pagination
        $qb->setFirstResult(($currentPage - 1) * $maxPerPage)
            ->setMaxResults($maxPerPage);

        // Retourner un Paginator
        return new Paginator($qb->getQuery());
    }


    public function getCoursFilling()
    {
        return $this->createQueryBuilder('c')
            ->select('NEW App\DTO\CoursFillingDTO(
            c.id,
            c.dateCours,
            c.nbInscriptionMax,
            COUNT(u.id)
        )')
            ->leftJoin('c.usersCours', 'u')
            ->where('c.statusCours = 1')
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

}
