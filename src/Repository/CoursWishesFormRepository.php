<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CoursWishesForm;
use App\Entity\User;
use App\Enum\StatusCoursWishesFormEnum;
use App\Helper\SaisonHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoursWishesForm>
 */
class CoursWishesFormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoursWishesForm::class);
    }

    /**
     * @return Paginator<CoursWishesForm>
     */
    public function paginatePending(int $page, int $limit): Paginator
    {
        $query = $this->createQueryBuilder('f')
            ->andWhere('f.status = :status')
            ->setParameter('status', StatusCoursWishesFormEnum::EN_ATTENTE->value)
            ->orderBy('f.submittedAt', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query);
    }

    public function findValidatedForEmail(string $email, string $saison): ?CoursWishesForm
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.email = :email')
            ->andWhere('f.saison = :saison')
            ->andWhere('f.status = :status')
            ->andWhere('f.user IS NULL')
            ->setParameter('email', $email)
            ->setParameter('saison', $saison)
            ->setParameter('status', StatusCoursWishesFormEnum::VALIDE->value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCurrentForUser(User $user, string $saison): ?CoursWishesForm
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user')
            ->andWhere('f.saison = :saison')
            ->setParameter('user', $user)
            ->setParameter('saison', $saison)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function hasValidatedFormForCurrentSaison(User $user): bool
    {
        $count = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->andWhere('f.user = :user')
            ->andWhere('f.saison = :saison')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('saison', SaisonHelper::current())
            ->setParameter('status', StatusCoursWishesFormEnum::VALIDE->value)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
