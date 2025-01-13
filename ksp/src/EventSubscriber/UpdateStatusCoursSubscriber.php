<?php

namespace App\EventSubscriber;

use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Event\UpdateStatusCoursEvent;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Serializer\CreateCoursDTOToCoursDenormalizer;
use App\Serializer\UpdateCoursDTOToCoursDenormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Serializer;

class UpdateStatusCoursSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly CoursRepository $coursRepository,
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly EntityManagerInterface $em,
    )
    {

    }

    public function onUpdateStatusCoursEvent($event): void
    {
        $coursArray = $this->coursRepository->getSuperLightAllCours();
        $archive = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ARCHIVE->value]);
        $enCours = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_COURS->value]);
        $passe = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::PASSE->value]);


        foreach ($coursArray as $cours) {
            $dateCours = $cours->getDateCours();
            $status = $cours->getStatusCours();
            $newStatus = null;

            if (
                $dateCours < new \DateTime('+ 2 hours') && $dateCours > new \DateTime('-1 month')
            ) {
                $newStatus = $passe;
            }

            if ($dateCours < new \DateTime('-1 month')) {
                $newStatus = $archive;
            }

            if($dateCours > new \DateTime() && $dateCours < new \DateTime('+' . $cours->getDuree() . " minutes") && $status === StatusCoursEnum::OUVERT->value){
                $newStatus = $enCours;
            }

            if($newStatus !== null){
                $cours->setStatusCours($newStatus->getId());
                $coursDTOSerializer = new Serializer([new UpdateCoursDTOToCoursDenormalizer($this->statusCoursRepository, $this->coursRepository)]);
                $cours = $coursDTOSerializer->denormalize($cours, Cours::class);
                $this->em->persist($cours);
                $this->em->flush();
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            UpdateStatusCoursEvent::class => 'onUpdateStatusCoursEvent',
        ];
    }
}
