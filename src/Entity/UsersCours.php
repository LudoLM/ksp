<?php

namespace App\Entity;

use App\Repository\UsersCoursRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UsersCoursRepository::class)]
class UsersCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:detail', 'cours:index', 'cours:detail'])]
    #[ORM\Column]
    private bool $isOnWaitingList;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[Groups(['cours:index', 'cours:detail'])]
    #[ORM\ManyToOne(inversedBy: 'usersCours')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[Groups(['user:detail'])]
    #[ORM\ManyToOne(inversedBy: 'usersCours')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Cours $cours = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isOnWaitingList(): ?bool
    {
        return $this->isOnWaitingList;
    }

    public function setIsOnWaitingList(bool $isOnWaitingList): static
    {
        $this->isOnWaitingList = $isOnWaitingList;

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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCours(): Cours
    {
        return $this->cours;
    }

    public function setCours(?Cours $cours): static
    {
        $this->cours = $cours;

        return $this;
    }
}
