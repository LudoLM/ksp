<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_cours = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'cours_list')]
    private Collection $user_list;

    #[ORM\Column]
    private ?int $tarif = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $Date_limite_inscription = null;

    #[ORM\Column]
    private ?int $nbInscriptionMax = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeCours $TypeCours = null;

    public function __construct()
    {
        $this->user_list = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateCours(): ?\DateTimeInterface
    {
        return $this->date_cours;
    }

    public function setDateCours(\DateTimeInterface $date_cours): self
    {
        $this->date_cours = $date_cours;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUserList(): Collection
    {
        return $this->user_list;
    }

    public function addUserList(User $userList): self
    {
        if (!$this->user_list->contains($userList)) {
            $this->user_list->add($userList);
            $userList->addCoursList($this);
        }

        return $this;
    }

    public function removeUserList(User $userList): self
    {
        if ($this->user_list->removeElement($userList)) {
            $userList->removeCoursList($this);
        }

        return $this;
    }

    public function getTarif(): ?int
    {
        return $this->tarif;
    }

    public function setTarif(int $tarif): self
    {
        $this->tarif = $tarif;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->Date_limite_inscription;
    }

    public function setDateLimiteInscription(?\DateTimeInterface $Date_limite_inscription): self
    {
        $this->Date_limite_inscription = $Date_limite_inscription;

        return $this;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(int $nbInscriptionMax): self
    {
        $this->nbInscriptionMax = $nbInscriptionMax;

        return $this;
    }

    public function getTypeCours(): ?TypeCours
    {
        return $this->TypeCours;
    }

    public function setTypeCours(?TypeCours $TypeCours): self
    {
        $this->TypeCours = $TypeCours;

        return $this;
    }
}
