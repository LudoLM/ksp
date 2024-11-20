<?php


namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class CreateCoursDTO
{

    #[Groups('cours:create')]
    public ?int $dureeCours = null;

    #[Groups('cours:create')]
    public ?\DateTimeInterface $dateCours = null;

    #[Groups('cours:create')]
    public ?string $description = null;

    #[Groups('cours:create')]
    public ?int $tarif = null;

    #[Groups('cours:create')]
    public ?int $nbInscriptionMax = null;

    #[Groups('cours:create')]
    public ?int $typeCours = null;

    #[Groups('cours:create')]
    public ?\DateTimeInterface $dateLimiteInscription = null;

    public function getDureeCours(): ?int
    {
        return $this->dureeCours;
    }

    public function getDateCours(): ?\DateTimeInterface
    {
        return $this->dateCours;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTarif(): ?int
    {
        return $this->tarif;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function getTypeCours(): ?int
    {
        return $this->typeCours;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }


}
?>