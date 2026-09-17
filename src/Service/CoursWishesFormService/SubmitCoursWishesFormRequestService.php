<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\DTO\SubmitCoursWishesFormDTO;
use App\Entity\CoursWishesForm;
use App\Entity\User;
use App\Exception\InvalidCoursWishesFormSubmissionException;
use App\Repository\PackRepository;
use App\Repository\SeasonPlanningSlotRepository;

readonly class SubmitCoursWishesFormRequestService
{
    public function __construct(
        private PackRepository $packRepository,
        private SeasonPlanningSlotRepository $seasonPlanningSlotRepository,
        private CheckExistingAccountService $checkExistingAccountService,
        private SubmitCoursWishesFormService $submitService,
    ) {
    }

    public function handle(SubmitCoursWishesFormDTO $dto, ?User $user, bool $filledByAdmin, ?CoursWishesForm $targetForm = null): SubmitCoursWishesFormResult
    {
        if (!$user instanceof User) {
            if (null === $dto->nom || '' === trim($dto->nom)) {
                throw new InvalidCoursWishesFormSubmissionException('Le nom est requis.');
            }
            if (null === $dto->prenom || '' === trim($dto->prenom)) {
                throw new InvalidCoursWishesFormSubmissionException('Le prénom est requis.');
            }
            if (null === $dto->telephone || '' === trim($dto->telephone)) {
                throw new InvalidCoursWishesFormSubmissionException('Le téléphone est requis.');
            }
        }

        $pack = $this->packRepository->find($dto->packSouhaiteId);
        if (null === $pack) {
            throw new InvalidCoursWishesFormSubmissionException('Pack introuvable');
        }

        $creneauPrimaire = $this->seasonPlanningSlotRepository->find($dto->creneauPrimaireId);
        if (null === $creneauPrimaire) {
            throw new InvalidCoursWishesFormSubmissionException('Créneau prioritaire introuvable');
        }

        $creneauSecondaire = null !== $dto->creneauSecondaireId ? $this->seasonPlanningSlotRepository->find($dto->creneauSecondaireId) : null;

        // Le check "compte déjà existant" ne concerne que la toute première
        // soumission anonyme : quand $targetForm est fourni (lien de correction),
        // le dossier à mettre à jour est déjà identifié par le token, ce check
        // n'a pas lieu d'être.
        if (!$user instanceof User && !$targetForm instanceof CoursWishesForm && $this->checkExistingAccountService->handleIfExists($dto->email)) {
            return SubmitCoursWishesFormResult::accountAlreadyExists();
        }

        $form = $this->submitService->submit(
            email: $dto->email,
            user: $user,
            nom: $dto->nom,
            prenom: $dto->prenom,
            telephone: $dto->telephone,
            creneauPrimaire: $creneauPrimaire,
            creneauSecondaire: $creneauSecondaire,
            packSouhaite: $pack,
            modeReglement: $dto->modeReglement,
            filledByAdmin: $filledByAdmin,
            targetForm: $targetForm,
        );

        return SubmitCoursWishesFormResult::submitted($form);
    }
}
