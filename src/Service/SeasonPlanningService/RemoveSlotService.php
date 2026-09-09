<?php

declare(strict_types=1);

namespace App\Service\SeasonPlanningService;

use App\Entity\SeasonPlanningSlot;
use App\Entity\TypeCours;
use App\Repository\CoursWishesFormRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class RemoveSlotService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CoursWishesFormRepository $wishesFormRepository,
    ) {
    }

    /**
     * Retire ce cours des options du créneau. Si c'était la dernière option,
     * le créneau lui-même n'a plus de sens : il est supprimé, et les dossiers
     * d'inscription qui le référençaient sont dénoués (jamais bloqués).
     */
    public function removeTypeCoursFromSlot(SeasonPlanningSlot $slot, TypeCours $typeCours): void
    {
        $slot->removeTypeCoursOption($typeCours);

        if ($slot->getTypeCoursOptions()->isEmpty()) {
            foreach ($this->wishesFormRepository->findByCreneau($slot) as $form) {
                if ($form->getCreneauPrimaire() === $slot) {
                    $form->setCreneauPrimaire(null);
                }
                if ($form->getCreneauSecondaire() === $slot) {
                    $form->setCreneauSecondaire(null);
                }
            }

            $this->em->remove($slot);
        }

        $this->em->flush();
    }
}
