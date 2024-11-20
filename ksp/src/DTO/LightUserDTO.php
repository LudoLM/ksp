<?php

namespace App\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

class LightUserDTO
{
    public function __construct(int $id, string $prenom, string $nom)
    {
        $this->id = $id;
        $this->prenom = $prenom;
        $this->nom = $nom;
    }

    #[Groups('cours:index')]
    public readonly int $id;

    #[Groups('cours:index')]
    public readonly string $prenom;

    #[Groups('cours:index')]
    public readonly string $nom;

}