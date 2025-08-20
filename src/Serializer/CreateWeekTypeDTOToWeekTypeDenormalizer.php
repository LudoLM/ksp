<?php

namespace App\Serializer;

use App\DTO\CreateWeekTypeDTO;
use App\Entity\CoursWeekType;
use App\Entity\WeekType;
use App\Repository\TypeCoursRepository;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class CreateWeekTypeDTOToWeekTypeDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private TypeCoursRepository $typeCoursRepository,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        if (!$data instanceof CreateWeekTypeDTO) {
            throw new \Exception('Instance de createWeekTypeDTO attendue');
        }

        $weekType = new WeekType();
        $weekType->setName($data->name);

        foreach ($data->weekTypeArray as $item) {
            $coursWeekType = new CoursWeekType();
            $typeCours = $this->typeCoursRepository->find($item->typeCours['id']);

            if (null === $typeCours) {
                throw new \InvalidArgumentException("Le TypeCours avec l'ID {$item->typeCours['id']} est introuvable pour un cours de la semaine type.");
            }
            $coursWeekType->setTypeCours($typeCours);

            $coursWeekType->setDaySelected($item->daySelected);
            $coursWeekType->setHasPriority($item->hasPriority);
            $coursWeekType->setTimeSelected(new \DateTime($item->timeSelected));
            $coursWeekType->setHasLimitOfOneCoursPerWeek($item->hasLimitOfOneCoursPerWeek);
            $coursWeekType->setDuree($item->duree);
            $coursWeekType->setNbInscriptionMax($item->nbInscriptionMax);
            $coursWeekType->setSpecialNote($item->specialNote);

            $weekType->addCoursWeekType($coursWeekType);
        }

        return $weekType;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return WeekType::class === $type;
    }
}
