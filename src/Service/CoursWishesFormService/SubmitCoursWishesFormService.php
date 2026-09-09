<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\SeasonPlanningSlot;
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
        ?string $nom,
        ?string $prenom,
        ?string $telephone,
        ?SeasonPlanningSlot $creneauPrimaire,
        ?SeasonPlanningSlot $creneauSecondaire,
        Pack $packSouhaite,
        string $modeReglement,
        bool $filledByAdmin = false,
    ): CoursWishesForm {
        $saison = SaisonHelper::current();
        $form = $user instanceof User ? $this->repository->findCurrentForUser($user, $saison) : null;

        // Reprend un dossier anonyme existant (jamais lié à un compte) pour le
        // même email : couvre à la fois la resoumission d'un prospect sans
        // compte, et le cas où un admin remplit le dossier d'un user dont
        // l'email avait déjà un dossier anonyme en attente.
        if (!$form instanceof CoursWishesForm) {
            $form = $this->repository->findCurrentForEmail($email, $saison);
        }

        if (!$form instanceof CoursWishesForm) {
            $form = new CoursWishesForm();
            $form->setSaison($saison);
            $form->setSubmittedAt(new \DateTimeImmutable());
            $this->em->persist($form);
        }

        $form->setEmail($email);
        $form->setUser($user);
        $form->setNom($user instanceof User ? null : $nom);
        $form->setPrenom($user instanceof User ? null : $prenom);
        $form->setTelephone($user instanceof User ? null : $telephone);
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
