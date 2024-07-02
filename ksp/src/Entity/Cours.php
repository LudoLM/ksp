<?php

namespace App\Entity;

use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cours:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['cours:read'])]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['cours:read'])]
    private ?\DateTimeInterface $dateCours = null;

    #[ORM\Column(length: 255)]
    #[Groups(['cours:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['cours:read'])]
    private ?int $tarif = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['cours:read'])]
    private ?\DateTimeInterface $dateLimiteInscription = null;

    #[ORM\Column]
    #[Groups(['cours:read'])]
    private ?int $nbInscriptionMax = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cours:read'])]
    private ?TypeCours $typeCours = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cours:read'])]
    private ?StatusCours $statusCours = null;

    public function __construct()
    {
        $this->user_list = new ArrayCollection();
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

    #[Groups(['cours:read'])]
    public function getStatusCoursCssClass(): string
    {
        return StatusCoursEnum::getCssClassByLibelle($this->statusCours->getLibelle());
    }
}
