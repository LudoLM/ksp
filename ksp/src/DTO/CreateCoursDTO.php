<?php


namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class CreateCoursDTO
{

    #[Assert\NotBlank(message: "La durée ne peut pas être vide.")]
    #[Assert\GreaterThanOrEqual(1, message: "La durée du cours doit être positive")]
    #[Groups('cours:create')]
    public ?int $dureeCours = null;

    #[Assert\NotBlank(message: "La date du cours ne peut pas être vide.")]
    #[Assert\GreaterThanOrEqual('today', message: "La date du cours doit être supérieure ou égale à la date du jour.")]
    #[Groups('cours:create')]
    public ?\DateTimeInterface $dateCours = null;

    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    #[Groups('cours:create')]
    public ?string $description = null;

    #[Assert\NotBlank(message: "Le nombre d'inscription max ne peut pas être vide.")]
    #[Assert\GreaterThanOrEqual(1, message: "Le nombre d'inscriptions max doit être positive.")]
    #[Groups('cours:create')]
    public ?int $nbInscriptionMax = null;

    #[Assert\NotBlank(message: "Le type de cours ne peut pas être vide.")]
    #[Groups('cours:create')]
    public ?int $typeCours = null;

    #[Assert\NotBlank(message: "La date limite d'inscription ne peut pas être vide.")]
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