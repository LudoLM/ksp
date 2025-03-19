<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:index', 'user:detail', 'cours:index', 'cours:detail'])]
    private ?int $id = null;

    #[Groups(['user:detail'])]
    #[ORM\Column(length: 180)]
    public string $email;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:detail', 'cours:detail', 'cours:index'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:detail', 'cours:detail', 'cours:index'])]
    private string $prenom;

    #[Groups(['user:detail'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;
    #[Groups(['user:detail'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePostal = null;

    #[Groups(['user:detail'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commune = null;
    #[Groups(['user:detail'])]
    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone ne doit pas être vide.')]
    #[Assert\Regex(
        pattern: '/^\d{10}$/',
        message: 'Le numéro de téléphone doit contenir exactement 10 chiffres.'
    )]
    private string $telephone;

    #[Groups(['user:detail'])]
    #[ORM\Column]
    private int $nombreCours;

    /**
     * @var Collection<int, HistoriquePaiement>
     */
    #[Groups(['user:detail'])]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: HistoriquePaiement::class, orphanRemoval: true)]
    private Collection $historiquePaiements;

    /**
     * @var Collection<int, UsersCours>
     */
    #[Groups(['user:detail'])]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UsersCours::class, orphanRemoval: true)]
    private Collection $usersCours;

    public function __construct()
    {
        $this->historiquePaiements = new ArrayCollection();
        $this->usersCours = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $payload['id'] = $this->getId();
        $payload['email'] = $this->getEmail();
        $payload['prenom'] = $this->getPrenom();
        $event->setData($payload);
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(string $commune): self
    {
        $this->commune = $commune;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getNom();  // or some string field in your Vegetal Entity
    }

    public function getNombreCours(): ?int
    {
        return $this->nombreCours;
    }

    public function setNombreCours(int $nombreCours): static
    {
        $this->nombreCours = $nombreCours;

        return $this;
    }

    /**
     * @return Collection<int, HistoriquePaiement>
     */
    public function getHistoriquePaiements(): Collection
    {
        return $this->historiquePaiements;
    }

    public function addHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        if (!$this->historiquePaiements->contains($historiquePaiement)) {
            $this->historiquePaiements->add($historiquePaiement);
            $historiquePaiement->setUser($this);
        }

        return $this;
    }

    public function removeHistoriquePaiement(HistoriquePaiement $historiquePaiement): static
    {
        // set the owning side to null (unless already changed)
        if ($this->historiquePaiements->removeElement($historiquePaiement) && $historiquePaiement->getUser() === $this) {
            $historiquePaiement->setUser(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, UsersCours>
     */
    public function getUsersCours(): Collection
    {
        return $this->usersCours;
    }

    public function addUsersCour(UsersCours $usersCours): static
    {
        if (!$this->usersCours->contains($usersCours)) {
            $this->usersCours->add($usersCours);
            $usersCours->setUser($this);
        }

        return $this;
    }

    public function removeUsersCour(UsersCours $usersCours): static
    {
        // set the owning side to null (unless already changed)
        if ($this->usersCours->removeElement($usersCours) && $usersCours->getUser() === $this) {
            $usersCours->setUser(null);
        }

        return $this;
    }
}
