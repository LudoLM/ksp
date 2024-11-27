<?php

namespace App\Serializer;


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
        /** @var \App\DTO\CreateCoursDTO $data */
        if (!$data instanceof \App\DTO\CreateCoursDTO) {
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
        $cours->setDescription($data->getDescription());
        $cours->setNbInscriptionMax($data->getNbInscriptionMax());
        $cours->setTypeCours($this->typeCoursRepository->find($data->getTypeCours()));
        $cours->setDateLimiteInscription($data->getDateLimiteInscription());
        return $cours;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return Cours::class === $type;
    }

}
