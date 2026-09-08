<?php

declare(strict_types=1);

namespace App\Service\SeasonPlanningService;

use App\Entity\SeasonPlanning;
use App\Entity\SeasonPlanningSlot;
use App\Entity\TypeCours;
use App\Helper\SaisonHelper;
use App\Repository\SeasonPlanningRepository;
use App\Repository\SeasonPlanningSlotRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

readonly class AddSlotService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SeasonPlanningRepository $planningRepository,
        private SeasonPlanningSlotRepository $slotRepository,
    ) {
    }

    /**
     * Trouve ou crée le créneau (jour/heure) de la saison en cours, puis y
     * ajoute ce cours parmi les options possibles (aucun effet si déjà présent).
     * Un membre s'inscrit au créneau, jamais à une option précise : ajouter
     * un 2e cours sur le même jour/heure crée une alternance, pas un nouveau
     * créneau séparé.
     */
    public function addSlot(int $daySelected, \DateTimeInterface $timeSelected, TypeCours $typeCours): SeasonPlanningSlot
    {
        $saison = SaisonHelper::current();
        $planning = $this->planningRepository->findBySaison($saison) ?? $this->createPlanning($saison);

        $slot = $this->slotRepository->findByDayTime($planning, $daySelected, $timeSelected);

        if (!$slot instanceof SeasonPlanningSlot) {
            $slot = new SeasonPlanningSlot();
            $slot->setSeasonPlanning($planning);
            $slot->setDaySelected($daySelected);
            $slot->setTimeSelected($timeSelected);
            $this->em->persist($slot);
        }

        $slot->addTypeCoursOption($typeCours);

        $this->em->flush();

        return $slot;
    }

    /**
     * Crée et flush immédiatement le planning de la saison, pour qu'il ait un
     * id avant d'être utilisé comme paramètre de requête ou lié à un créneau.
     * Si une autre requête a créé entre-temps le planning de cette même saison,
     * la contrainte unique sur `saison` fait échouer notre insertion : on
     * abandonne alors la nôtre et on réutilise celle déjà en base.
     */
    private function createPlanning(string $saison): SeasonPlanning
    {
        $planning = new SeasonPlanning();
        $planning->setSaison($saison);
        $this->em->persist($planning);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            $this->em->detach($planning);

            return $this->planningRepository->findBySaison($saison)
                ?? throw new \RuntimeException('Impossible de créer ou retrouver le planning de la saison en cours.');
        }

        return $planning;
    }
}
