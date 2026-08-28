<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\Entity\CoursWeekType;
use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\User;
use App\Enum\StatusCoursWishesFormEnum;
use App\Helper\SaisonHelper;
use App\Repository\CoursWishesFormRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class SubmitCoursWishesFormService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CoursWishesFormRepository $repository,
    ) {
    }

    public function submit(
        string $email,
        ?User $user,
        ?CoursWeekType $creneauPrimaire,
        ?CoursWeekType $creneauSecondaire,
        Pack $packSouhaite,
        string $modeReglement,
        bool $filledByAdmin = false,
    ): CoursWishesForm {
        $saison = SaisonHelper::current();
        $form = $user instanceof User ? $this->repository->findCurrentForUser($user, $saison) : null;

        if (!$form instanceof CoursWishesForm) {
            $form = new CoursWishesForm();
            $form->setSaison($saison);
            $form->setSubmittedAt(new \DateTimeImmutable());
            $this->em->persist($form);
        }

        $form->setEmail($email);
        $form->setUser($user);
        $form->setCreneauPrimaire($creneauPrimaire);
        $form->setCreneauSecondaire($creneauSecondaire);
        $form->setPackSouhaite($packSouhaite);
        $form->setModeReglement($modeReglement);
        $form->setFilledByAdmin($filledByAdmin);

        // Toute (re)soumission remet le dossier en revue, même s'il était déjà traité.
        $form->setStatus(StatusCoursWishesFormEnum::EN_ATTENTE->value);
        $form->setValidatedAt(null);
        $form->setValidatedBy(null);
        $form->setCorrectionReason(null);

        $this->em->flush();

        return $form;
    }
}
