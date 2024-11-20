<?php

namespace App\Entity;

use App\Repository\UsersCoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UsersCoursRepository::class)]
class UsersCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:detail', 'cours:index'])]
    #[ORM\Column]
    private ?bool $isEnAttente = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[Groups(['cours:index'])]
    #[ORM\ManyToOne(inversedBy: 'usersCours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Cours>
     */
    #[Groups(['user:detail'])]
    #[ORM\ManyToOne(inversedBy: 'usersCours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cours $cours = null;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEnAttente(): ?bool
    {
        return $this->isEnAttente;
    }

    public function setEnAttente(bool $isEnAttente): static
    {
        $this->isEnAttente = $isEnAttente;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
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

    public function getCours(): ?Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;

        return $this;
    }
}
