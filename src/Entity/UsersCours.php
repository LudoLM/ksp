<?php

namespace App\Entity;

use App\Repository\UsersCoursRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UsersCoursRepository::class)]
#[Index(fields: ['createdAt', 'user'], name: 'idx_created_user')]
#[Index(fields: ['unsubscribedAt', 'user'], name: 'idx_unsubscribed_user')]
class UsersCours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:profile', 'cours:index', 'cours:detail', 'usersCours:read'])]
    #[ORM\Column]
    private bool $isOnWaitingList;

    #[Groups(['user:profile', 'usersCours:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[Groups(['user:profile', 'cours:index', 'cours:detail', 'usersCours:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $unsubscribedAt = null;

    #[Groups(['cours:index', 'cours:detail', 'usersCours:read'])]
    #[ORM\ManyToOne(inversedBy: 'usersCours')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[Groups(['user:profile', 'usersCours:read'])]
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
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUnsubscribedAt(): ?\DateTimeImmutable
    {
        return $this->unsubscribedAt;
    }

    public function setUnsubscribedAt(?\DateTimeImmutable $unsubscribedAt): static
    {
        $this->unsubscribedAt = $unsubscribedAt;

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
