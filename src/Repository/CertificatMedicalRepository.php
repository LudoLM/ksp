<?php

namespace App\Repository;

use App\Entity\CertificatMedical;
use App\Enum\StatusCertificateEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CertificatMedical>
 */
class CertificatMedicalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CertificatMedical::class);
    }

    /**
     * @return Paginator<CertificatMedical>
     */
    public function paginatePending(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->setParameter('status', 'Pending')
            ->orderBy('c.uploadedAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query);
    }

    /**
     * @return CertificatMedical[]
     */
    public function findExpirable(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.status = :status')
            ->andWhere('c.validUntil < :now')
            ->setParameter('status', StatusCertificateEnum::APPROVED->value)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
}
