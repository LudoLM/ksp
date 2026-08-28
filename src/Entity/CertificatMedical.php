<?php

namespace App\Entity;

use App\Repository\CertificatMedicalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CertificatMedicalRepository::class)]
class CertificatMedical
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:detail'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'certificatMedicaux')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private string $certificateFilename;
    #[Groups(['user:detail'])]
    #[ORM\Column(length: 20)]
    private string $status;
    #[Groups(['user:detail'])]
    #[ORM\Column]
    private \DateTimeImmutable $uploadedAt;
    #[Groups(['user:detail'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $validUntil = null;

    #[Groups(['user:detail'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $uploadedBy = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCertificateFilename(): string
    {
        return $this->certificateFilename;
    }

    public function setCertificateFilename(string $certificateFilename): static
    {
        $this->certificateFilename = $certificateFilename;

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

    public function getUploadedAt(): \DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getValidUntil(): ?\DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTimeImmutable $validUntil): static
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;

        return $this;
    }

    public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(?User $uploadedBy): static
    {
        $this->uploadedBy = $uploadedBy;

        return $this;
    }
}
