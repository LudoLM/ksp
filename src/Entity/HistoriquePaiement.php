<?php

namespace App\Entity;

use App\Repository\HistoriquePaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: HistoriquePaiementRepository::class)]
class HistoriquePaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:profile'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $checkoutId;

    #[ORM\ManyToOne(inversedBy: 'historiquePaiements')]
    private ?User $user = null;

    #[Groups(['user:profile'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $date;
    #[Groups(['user:profile', 'historique_paiements:index'])]
    #[ORM\ManyToOne(inversedBy: 'historiquePaiements')]
    private ?Pack $pack = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCheckoutId(): ?string
    {
        return $this->checkoutId;
    }

    public function setCheckoutId(string $checkoutId): static
    {
        $this->checkoutId = $checkoutId;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPack(): ?Pack
    {
        return $this->pack;
    }

    public function setPack(?Pack $pack): static
    {
        $this->pack = $pack;

        return $this;
    }
}
