<?php

namespace App\Serializer;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @method array getSupportedTypes(?string $format)
 */
class UpdateCoursDTOToCoursDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly CoursRepository $coursRepository,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        /**
         * @param \App\DTO\SuperLightCoursDTO $data
         */
        if (!$data instanceof \App\DTO\SuperLightCoursDTO) {
            throw new \Exception('Expected instance of SuperLightCoursDTO');
        }
        $cours = $this->coursRepository->find($data->getId());
        $cours->setStatusCours($this->statusCoursRepository->find($data->getStatusCours()));

        return $cours;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return Cours::class === $type;
    }
}
