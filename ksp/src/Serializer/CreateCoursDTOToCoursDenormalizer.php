<?php

namespace App\Serializer;


use App\DTO\CreateCoursDTO;
use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CreateCoursDTOToCoursDenormalizer implements DenormalizerInterface
{

    public function __construct(
        private TypeCoursRepository $typeCoursRepository,
        private StatusCoursRepository $statusCoursRepository
    )
    {
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        /** @var CreateCoursDTO $data */
        if (!$data instanceof CreateCoursDTO) {
            throw new \Exception('Expected instance of CreateCoursDTO');
        }
        if (array_key_exists('object_to_populate', $context) &&  $context['object_to_populate'] instanceof Cours) {
            $cours = $context['object_to_populate'];
        } else {
            $cours = new Cours();
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_CREATION->value]));

        }
        $cours->setDuree($data->getDureeCours());
        $cours->setDateCours($data->getDateCours());
        $cours->setSpecialNote($data->getSpecialNote());
        $cours->setNbInscriptionMax($data->getNbInscriptionMax());
        $cours->setTypeCours($this->typeCoursRepository->find($data->getTypeCours()));
        return $cours;
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return Cours::class === $type;
    }

}
