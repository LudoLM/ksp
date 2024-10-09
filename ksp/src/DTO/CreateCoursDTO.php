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
}
?>