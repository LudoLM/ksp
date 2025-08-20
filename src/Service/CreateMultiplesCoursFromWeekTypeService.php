<?php

namespace App\Service;

use App\DTO\AssignWeekDTO;
use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Repository\WeekTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class CreateMultiplesCoursFromWeekTypeService
{
    public function __construct(
        private WeekTypeRepository $weekTypeRepository,
        private TypeCoursRepository $typeCoursRepository,
        private StatusCoursRepository $statusCoursRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function create(AssignWeekDTO $assignWeekDTO): void
    {
        try {
            $weekTypeId = $assignWeekDTO->weekTypeId;
            $dateMonday = new \DateTime($assignWeekDTO->dateMonday);
            $weekType = $this->weekTypeRepository->find($weekTypeId);
            if (null === $weekType) {
                throw new \InvalidArgumentException("Le type de semaine (WeekType) avec l'ID {$weekTypeId} n'a pas été trouvé.");
            }
            $statusCours = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_CREATION->value]);
            if (null === $statusCours) {
                throw new \RuntimeException("Le statut 'EN_CREATION' est introuvable.");
            }
            foreach ($assignWeekDTO->coursList as $coursWeekType) {
                $dateSelected = clone $dateMonday;
                $dateSelected->modify("{$coursWeekType->daySelected} day");
                $time = \DateTime::createFromFormat('H:i', $coursWeekType->timeSelected);
                if (false === $time) {
                    throw new \InvalidArgumentException("Le format de l'heure '{$coursWeekType->timeSelected}' est invalide.");
                }
                $dateSelected->setTime((int) $time->format('H'), (int) $time->format('i'), (int) $time->format('s'));
                $cours = new Cours();
                $typeCours = $this->typeCoursRepository->find($coursWeekType->typeCours['id']);
                if (null === $typeCours) {
                    throw new \InvalidArgumentException("Le type de cours avec l'ID {$coursWeekType->typeCours['id']} n'a pas été trouvé.");
                }
                $cours->setTypeCours($typeCours);
                $cours->setSpecialNote($coursWeekType->specialNote);
                $cours->setNbInscriptionMax($coursWeekType->nbInscriptionMax);
                $cours->setCreatedAt(new \DateTime());
                $cours->setLaunchedAt(new \DateTime());
                $cours->setDateCours($dateSelected);
                $cours->setStatusCours($statusCours);
                $cours->setHasPriority($coursWeekType->hasPriority);
                $cours->setHasLimitOfOneCoursPerWeek($coursWeekType->hasLimitOfOneCoursPerWeek);
                $cours->setDuree($coursWeekType->duree);
                $this->em->persist($cours);
            }
            $this->em->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Une erreur est survenue lors de la création des cours de la semaine. '.$e->getMessage(), 0, $e);
        }
    }
}
