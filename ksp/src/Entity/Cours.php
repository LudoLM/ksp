<?php

namespace App\Entity;

use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cours:index', 'cours:detail', "cours:create", "user:detail"])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['cours:index', 'cours:detail', "cours:create"])]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['cours:index', 'cours:detail', "cours:create", "user:detail"])]
    private ?\DateTimeInterface $dateCours = null;

    #[ORM\Column(length: 255)]
    #[Groups(['cours:index', 'cours:detail', "cours:create"])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['cours:index', 'cours:detail', "cours:create"])]
    private ?int $tarif = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['cours:index', 'cours:detail', "cours:create"])]
    private ?\DateTimeInterface $dateLimiteInscription = null;

    #[ORM\Column]
    #[Groups(['cours:index', 'cours:detail', "cours:create"])]
    private ?int $nbInscriptionMax = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cours:index', 'cours:detail', "cours:create", "user:detail"])]
    private ?TypeCours $typeCours = null;


    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cours:index', 'cours:detail', "cours:create", "user:detail"])]
    private ?StatusCours $statusCours = null;



    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'cours_list')]
    #[Groups(['cours:index','cours:detail'])]
    private Collection $users;
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getDuree(): ?int
    {
        return $this->duree;
    }

    /**
     * @param int|null $duree
     */
    public function setDuree(?int $duree): void
    {
        $this->duree = $duree;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDateCours(): ?\DateTimeInterface
    {
        return $this->dateCours;
    }

    /**
     * @param \DateTimeInterface|null $dateCours
     */
    public function setDateCours(?\DateTimeInterface $dateCours): void
    {
        $this->dateCours = $dateCours;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int|null
     */
    public function getTarif(): ?int
    {
        return $this->tarif;
    }

    /**
     * @param int|null $tarif
     */
    public function setTarif(?int $tarif): void
    {
        $this->tarif = $tarif;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    /**
     * @param \DateTimeInterface|null $dateLimiteInscription
     */
    public function setDateLimiteInscription(?\DateTimeInterface $dateLimiteInscription): void
    {
        $this->dateLimiteInscription = $dateLimiteInscription;
    }

    /**
     * @return int|null
     */
    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    /**
     * @param int|null $nbInscriptionMax
     */
    public function setNbInscriptionMax(?int $nbInscriptionMax): void
    {
        $this->nbInscriptionMax = $nbInscriptionMax;
    }

    /**
     * @return TypeCours|null
     */
    public function getTypeCours(): ?TypeCours
    {
        return $this->typeCours;
    }

    /**
     * @param TypeCours|null $typeCours
     */
    public function setTypeCours(?TypeCours $typeCours): void
    {
        $this->typeCours = $typeCours;
    }

    /**
     * @return StatusCours|null
     */
    public function getStatusCours(): ?StatusCours
    {
        return $this->statusCours;
    }

    /**
     * @param StatusCours|null $statusCours
     */
    public function setStatusCours(?StatusCours $statusCours): void
    {
        $this->statusCours = $statusCours;
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection $users
     */
    public function setUsers(Collection $users): void
    {
        $this->users = $users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCoursList($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeCoursList($this);
        }

        return $this;
    }

}
