<?php

namespace App\Repository;

use App\Entity\HistoriquePaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HistoriquePaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriquePaiement::class);
    }

    //    /**
    //     * @return HistoriquePaiement[] Returns an array of HistoriquePaiement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('h.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?HistoriquePaiement
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findQuantityOfEachPacksPerMonth(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT p.id, p.nom AS name, p.tarif AS price, COUNT(hp.id) AS quantity,
               MONTH(hp.date) AS month, YEAR(hp.date) AS year
        FROM historique_paiement hp
        LEFT JOIN pack p ON hp.pack_id = p.id
        WHERE hp.date >= :startDate AND hp.date <= :endDate
        GROUP BY p.id, year, month
        ORDER BY year ASC, month ASC, quantity DESC
    ';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
        ]);

        return $result->fetchAllAssociative();
    }
}
