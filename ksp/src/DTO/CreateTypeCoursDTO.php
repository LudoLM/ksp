<?php


namespace App\DTO;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateTypeCoursDTO
{
    public ?string $nom = null;

    /**
     * @var UploadedFile|null
     */
    public ?UploadedFile $image = null;
}
?>