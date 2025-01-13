<?php

namespace App\Serializer;

use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @method array getSupportedTypes(?string $format)
 */
class UpdateCoursDTOToCoursDenormalizer implements DenormalizerInterface
{


    public function __construct(
        private StatusCoursRepository $statusCoursRepository,
        private CoursRepository $CoursRepository
    )
    {
    }


    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        /** @var \App\DTO\CreateCoursDTO $data */
        if (!$data instanceof \App\DTO\SuperLightCoursDTO) {
            throw new \Exception('Expected instance of SuperLightCoursDTO');
        }
        $cours = $this->CoursRepository->find($data->getId());
        $cours->setStatusCours($this->statusCoursRepository->find($data->getStatusCours()));
        return $cours;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return Cours::class === $type;
    }

}

