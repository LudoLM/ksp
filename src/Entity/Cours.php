<?php

namespace App\Entity;

use App\Entity\Abstracts\Coursbase;
use App\Repository\CoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours extends Coursbase
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update', 'user:detail', 'cours_filling:index'])]
    private \DateTimeInterface $dateCours;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['cours:index', 'cours:create'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['cours:index', 'cours:create', 'cours:update', 'cours:detail'])]
    private ?\DateTimeInterface $launchedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['cours:index', 'cours:update'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'cours')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update', 'user:detail'])]
    private StatusCours $statusCours;

    /**
     * @var Collection<int, UsersCours>
     */
    #[ORM\OneToMany(mappedBy: 'cours', targetEntity: UsersCours::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['cours:index', 'cours:detail', 'cours:create', 'cours:update', 'cours_filling:index'])]
    private Collection $usersCours;

    public function __construct()
    {
        $this->usersCours = new ArrayCollection();
    }

    public function getDateCours(): \DateTimeInterface
    {
        return $this->dateCours;
    }

    public function getDateImmutable(): \DateTimeImmutable
    {
        $dateCours = \DateTime::createFromInterface($this->dateCours);

        return \DateTimeImmutable::createFromMutable($dateCours);
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getLaunchedAt(): \DateTimeInterface
    {
        return $this->launchedAt;
    }

    public function setLaunchedAt(\DateTimeInterface $launchedAt): void
    {
        $this->launchedAt = $launchedAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function setDateCours(\DateTimeInterface $dateCours): void
    {
        $this->dateCours = $dateCours;
    }

    public function getStatusCours(): StatusCours
    {
        return $this->statusCours;
    }

    public function setStatusCours(StatusCours $statusCours): void
    {
        $this->statusCours = $statusCours;
    }

    /**
     * @return Collection<int, UsersCours>
     */
    public function getUsersCours(): Collection
    {
        return $this->usersCours;
    }

    public function addUsersCours(UsersCours $usersCours): self
    {
        if (!$this->usersCours->contains($usersCours)) {
            $this->usersCours->add($usersCours);
            $usersCours->setCours($this);
        }

        return $this;
    }

    public function removeUsersCours(UsersCours $usersCours): self
    {
        // set the owning side to null (unless already changed)
        if ($this->usersCours->removeElement($usersCours) && $usersCours->getCours() === $this) {
            $usersCours->setCours(null);
        }

        return $this;
    }
}
