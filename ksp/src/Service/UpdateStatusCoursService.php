<?php

namespace App\Service;

use App\Entity\StatusCours;
use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class UpdateStatusCoursService
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StatusCoursRepository $statusCoursRepository
    )
    {
    }

    public function updateStatusCours($cours): array
    {
        $ouvert = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]);
        $archive = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ARCHIVE->value]);
        $enCours = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_COURS->value]);
        $passe = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::PASSE->value]);


        foreach ($cours as $cour) {
            $dateCours = $cour->getDateCours();
            $status = $cour->getStatusCours()->getLibelle();

            //Potentiellement pas utile
            if($cour->getNbInscriptionMax() > $cour->getUsersCours()->count() && $status !== StatusCoursEnum::EN_CREATION->value && $status !== StatusCoursEnum::ANNULE->value){
                $cour->setStatusCours($ouvert);
            }

            if (
                $dateCours < new \DateTime('+ 2 hours') && $dateCours > new \DateTime('-1 month')
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