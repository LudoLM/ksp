<?php

namespace App\Service;

use App\Entity\StatusCours;
use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateStatusCours
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CoursRepository $coursRepository,
        private readonly StatusCoursRepository $statusCoursRepository
    )
    {
    }

    public function updateStatusCours(): array
    {
        $cours = $this->coursRepository->findAllSortByDate();

        $archive = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ARCHIVE->value]);
        $enCours = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_COURS->value]);
        $passe = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::PASSE->value]);


        foreach ($cours as $cour) {
            $dateCours = $cour->getDateCours();
            $status = $cour->getStatusCours()->getLibelle();

            if (
                $dateCours < new \DateTime() && $dateCours > new \DateTime('-1 month')
            ) {
                $cour->setStatusCours($passe);
            }

            if ($dateCours < new \DateTime('-1 month')) {
                $cour->setStatusCours($archive);
            }

            if($dateCours > new \DateTime() && $dateCours < new \DateTime('+' . $cour->getDuree() . " minutes") && $status === StatusCoursEnum::OUVERT->value){
                $cour->setStatusCours($enCours);
            }

            $this->em->persist($cour);
        }

        $this->em->flush();

        return $cours;
    }
}