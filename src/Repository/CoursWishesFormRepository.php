<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CoursWishesForm;
use App\Entity\SeasonPlanningSlot;
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
            ->leftJoin('f.creneauPrimaire', 'cp')
            ->addSelect('cp')
            ->leftJoin('cp.typeCoursOptions', 'cpTypes')
            ->addSelect('cpTypes')
            ->leftJoin('f.creneauSecondaire', 'cs')
            ->addSelect('cs')
            ->leftJoin('cs.typeCoursOptions', 'csTypes')
            ->addSelect('csTypes')
            ->leftJoin('f.packSouhaite', 'p')
            ->addSelect('p')
            ->andWhere('f.status = :status')
            ->setParameter('status', StatusCoursWishesFormEnum::EN_ATTENTE->value)
            ->orderBy('f.submittedAt', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        return new Paginator($query, fetchJoinCollection: true);
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

    /**
     * Un dossier non lié à un compte (soumis par un prospect sans compte).
     * Restreint à `user IS NULL` pour ne jamais réutiliser/écraser le dossier
     * d'un adhérent déjà lié via une simple collision d'email.
     */
    public function findCurrentForEmail(string $email, string $saison): ?CoursWishesForm
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.email = :email')
            ->andWhere('f.saison = :saison')
            ->andWhere('f.user IS NULL')
            ->setParameter('email', $email)
            ->setParameter('saison', $saison)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return CoursWishesForm[]
     */
    public function findByCreneau(SeasonPlanningSlot $slot): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.creneauPrimaire = :slot OR f.creneauSecondaire = :slot')
            ->setParameter('slot', $slot)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, int> clé = id du SeasonPlanningSlot, valeur = nombre de dossiers
     */
    public function countByCreneauPrimaire(string $saison): array
    {
        $rows = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.creneauPrimaire) as slotId, COUNT(f.id) as cnt')
            ->andWhere('f.saison = :saison')
            ->andWhere('f.creneauPrimaire IS NOT NULL')
            ->setParameter('saison', $saison)
            ->groupBy('f.creneauPrimaire')
            ->getQuery()
            ->getResult();

        return array_column($rows, 'cnt', 'slotId');
    }

    /**
     * @return array<int, int> clé = id du SeasonPlanningSlot, valeur = nombre de dossiers
     */
    public function countByCreneauSecondaire(string $saison): array
    {
        $rows = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.creneauSecondaire) as slotId, COUNT(f.id) as cnt')
            ->andWhere('f.saison = :saison')
            ->andWhere('f.creneauSecondaire IS NOT NULL')
            ->setParameter('saison', $saison)
            ->groupBy('f.creneauSecondaire')
            ->getQuery()
            ->getResult();

        return array_column($rows, 'cnt', 'slotId');
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
