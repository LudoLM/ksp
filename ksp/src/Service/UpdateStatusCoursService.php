<?php

namespace App\Service;

use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class UpdateStatusCoursService
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StatusCoursRepository $statusCoursRepository
    )
    {
    }

    public function updateStatusCours(array $coursArray): array
    {
        $ouvert = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]);
        $archive = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ARCHIVE->value]);
        $enCours = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_COURS->value]);
        $passe = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::PASSE->value]);


        foreach ($coursArray as $cours) {
            $dateCours = $cours->getDateCours();
            $status = $cours->getStatusCours()->getLibelle();

            //Potentiellement pas utile
            if($cours->getNbInscriptionMax() > $cours->getUsersCours()->count() && $status !== StatusCoursEnum::EN_CREATION->value && $status !== StatusCoursEnum::ANNULE->value){
                $cours->setStatusCours($ouvert);
            }

            if (
                $dateCours < new \DateTime('+ 2 hours') && $dateCours > new \DateTime('-1 month')
            ) {
                $cours->setStatusCours($passe);
            }

            if ($dateCours < new \DateTime('-1 month')) {
                $cours->setStatusCours($archive);
            }

            if($dateCours > new \DateTime() && $dateCours < new \DateTime('+' . $cours->getDuree() . " minutes") && $status === StatusCoursEnum::OUVERT->value){
                $cours->setStatusCours($enCours);
            }



            $this->em->persist($cours);
        }

        $this->em->flush();

        return $coursArray;
    }
}