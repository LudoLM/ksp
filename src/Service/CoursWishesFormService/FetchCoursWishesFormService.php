<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\SeasonPlanningSlot;
use App\Repository\CoursWishesFormRepository;

readonly class FetchCoursWishesFormService
{
    public function __construct(
        private CoursWishesFormRepository $repository,
    ) {
    }

    public function getPendingForms(int $page, int $limit): array
    {
        $paginator = $this->repository->paginatePending($page, $limit);
        $totalItems = $paginator->count();

        $data = [];
        foreach ($paginator as $form) {
            $data[] = [
                'id' => $form->getId(),
                'email' => $form->getEmail(),
                'contactNom' => $form->getContactNom(),
                'contactPrenom' => $form->getContactPrenom(),
                'contactTelephone' => $form->getContactTelephone(),
                'saison' => $form->getSaison(),
                'status' => $form->getStatus(),
                'submittedAt' => $form->getSubmittedAt()->format('Y-m-d H:i:s'),
                'creneauPrimaire' => $this->describeSlot($form->getCreneauPrimaire()),
                'creneauSecondaire' => $this->describeSlot($form->getCreneauSecondaire()),
                'packSouhaite' => $form->getPackSouhaite()->getNom(),
                'modeReglement' => $form->getModeReglement(),
                'user' => $form->getUser()?->getId(),
            ];
        }

        return [
            'metadata' => [
                'total_items' => $totalItems,
                'current_page' => $page,
                'total_pages' => (int) ceil($totalItems / $limit),
            ],
            'data' => $data,
        ];
    }

    /**
     * @return array{id: int, daySelected: int, timeSelected: string, cours: string}|null
     */
    private function describeSlot(?SeasonPlanningSlot $slot): ?array
    {
        if (!$slot instanceof SeasonPlanningSlot) {
            return null;
        }

        return [
            'id' => $slot->getId(),
            'daySelected' => $slot->getDaySelected(),
            'timeSelected' => $slot->getTimeSelected()->format('H:i'),
            'cours' => implode('/', array_map(
                static fn (\App\Entity\TypeCours $typeCours): ?string => $typeCours->getLibelle(),
                $slot->getTypeCoursOptions()->toArray()
            )),
        ];
    }

    /**
     * Détermine, parmi les dossiers d'un utilisateur, celui de la saison donnée
     * à lui afficher. Statique : appelable depuis App\Entity\User (qui ne peut
     * pas recevoir d'injection de dépendances), même principe que
     * FetchCertificateService::selectCurrentCertificate.
     *
     * @param iterable<CoursWishesForm> $forms
     */
    public static function selectCurrentForm(iterable $forms, string $saison): ?CoursWishesForm
    {
        foreach ($forms as $form) {
            if ($form->getSaison() === $saison) {
                return $form;
            }
        }

        return null;
    }
}
