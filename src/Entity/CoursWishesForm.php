<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CoursWishesFormRepository;
use App\Service\Interface\Notification\RecipientInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CoursWishesFormRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_wishes_form_email_saison', columns: ['email', 'saison'])]
class CoursWishesForm implements RecipientInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['wishes_form:read', 'user:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['wishes_form:read'])]
    private string $email;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $telephone = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'coursWishesForms')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 20)]
    #[Groups(['wishes_form:read', 'user:detail'])]
    private string $saison;

    #[ORM\ManyToOne(targetEntity: SeasonPlanningSlot::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['wishes_form:read'])]
    private ?SeasonPlanningSlot $creneauPrimaire = null;

    #[ORM\ManyToOne(targetEntity: SeasonPlanningSlot::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['wishes_form:read'])]
    private ?SeasonPlanningSlot $creneauSecondaire = null;

    #[ORM\ManyToOne(targetEntity: Pack::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['wishes_form:read'])]
    private Pack $packSouhaite;

    #[ORM\Column(length: 20)]
    #[Groups(['wishes_form:read'])]
    private string $modeReglement;

    #[ORM\Column(length: 20)]
    #[Groups(['wishes_form:read', 'user:detail'])]
    private string $status;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['wishes_form:read'])]
    private ?string $correctionReason = null;

    #[ORM\Column]
    #[Groups(['wishes_form:read'])]
    private \DateTimeImmutable $submittedAt;

    #[ORM\Column(nullable: true)]
    #[Groups(['wishes_form:read'])]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $validatedBy = null;

    #[ORM\Column]
    private bool $filledByAdmin = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Nom à afficher pour contacter le demandeur : celui du compte lié s'il existe
     * (donnée à jour), sinon celui saisi anonymement à la soumission du dossier.
     * Seul point d'accès à utiliser pour afficher l'identité d'un dossier — ne
     * jamais lire $this->nom/$this->prenom/$this->telephone directement ailleurs.
     */
    #[Groups(['wishes_form:read'])]
    public function getContactNom(): ?string
    {
        return $this->user?->getNom() ?? $this->nom;
    }

    #[Groups(['wishes_form:read'])]
    public function getContactPrenom(): ?string
    {
        return $this->user?->getPrenom() ?? $this->prenom;
    }

    #[Groups(['wishes_form:read'])]
    public function getContactTelephone(): ?string
    {
        return $this->user?->getTelephone() ?? $this->telephone;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getSaison(): string
    {
        return $this->saison;
    }

    public function setSaison(string $saison): static
    {
        $this->saison = $saison;

        return $this;
    }

    public function getCreneauPrimaire(): ?SeasonPlanningSlot
    {
        return $this->creneauPrimaire;
    }

    public function setCreneauPrimaire(?SeasonPlanningSlot $creneauPrimaire): static
    {
        $this->creneauPrimaire = $creneauPrimaire;

        return $this;
    }

    public function getCreneauSecondaire(): ?SeasonPlanningSlot
    {
        return $this->creneauSecondaire;
    }

    public function setCreneauSecondaire(?SeasonPlanningSlot $creneauSecondaire): static
    {
        $this->creneauSecondaire = $creneauSecondaire;

        return $this;
    }

    public function getPackSouhaite(): Pack
    {
        return $this->packSouhaite;
    }

    public function setPackSouhaite(Pack $packSouhaite): static
    {
        $this->packSouhaite = $packSouhaite;

        return $this;
    }

    public function getModeReglement(): string
    {
        return $this->modeReglement;
    }

    public function setModeReglement(string $modeReglement): static
    {
        $this->modeReglement = $modeReglement;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCorrectionReason(): ?string
    {
        return $this->correctionReason;
    }

    public function setCorrectionReason(?string $correctionReason): static
    {
        $this->correctionReason = $correctionReason;

        return $this;
    }

    public function getSubmittedAt(): \DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeImmutable $submittedAt): static
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getValidatedBy(): ?User
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?User $validatedBy): static
    {
        $this->validatedBy = $validatedBy;

        return $this;
    }

    public function isFilledByAdmin(): bool
    {
        return $this->filledByAdmin;
    }

    public function setFilledByAdmin(bool $filledByAdmin): static
    {
        $this->filledByAdmin = $filledByAdmin;

        return $this;
    }
}
