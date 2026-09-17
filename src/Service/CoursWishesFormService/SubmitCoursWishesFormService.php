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
        ?CoursWishesForm $targetForm = null,
    ): CoursWishesForm {
        $saison = SaisonHelper::current();

        // Un lien de correction identifie déjà le dossier précis à mettre à
        // jour (résolu par token en amont)
        if ($targetForm instanceof CoursWishesForm) {
            $form = $targetForm;
        } else {
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
                $this->em->persist($form);
            }
        }

        // Recevoir le lien de correction suppose d'avoir déjà reçu l'email à la
        // bonne adresse : un email erroné n'a donc jamais pu être "corrigé" par ce
        // lien. Le champ n'a aucune raison légitime d'être modifiable ici — on
        // ignore la valeur envoyée par le client (défense en profondeur : le champ
        // est déjà lecture seule côté front) et on garde l'email déjà en base.
        if (!$targetForm instanceof CoursWishesForm) {
            $form->setEmail($email);
        }
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
        // submittedAt est rafraîchi à chaque soumission (pas seulement à la création) :
        // il reflète la dernière activité, utilisé pour trier la file d'attente admin
        // et détecter les dossiers EnAttente trop anciens.
        $form->setSubmittedAt(new \DateTimeImmutable());
        $form->setStatus(StatusCoursWishesFormEnum::EN_ATTENTE->value);
        $form->setReviewedAt(null);
        $form->setReviewedBy(null);
        $form->setCorrectionReason(null);

        // Un token d'inscription émis pour une précédente approbation ne doit pas
        // survivre à une resoumission : le dossier n'est plus VALIDE tant que
        // l'admin ne l'a pas re-revu, donc le lien déjà envoyé ne doit plus
        // permettre de créer un compte pour cette nouvelle demande non revue.
        $form->setRegistrationTokenHash(null);
        $form->setRegistrationTokenExpiresAt(null);

        // Le token de correction (s'il y en avait un) doit être explicitement
        // consommé ici : que la soumission vienne du lien de correction lui-même
        // (usage normal) ou d'un autre chemin, le dossier vient de changer d'état
        // et un ancien lien ne doit plus jamais redevenir valable.
        $form->setCorrectionTokenHash(null);
        $form->setCorrectionTokenExpiresAt(null);

        $this->em->flush();

        return $form;
    }
}
