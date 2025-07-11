<?php

namespace App\Service;

use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Serializer\UpdateCoursDTOToCoursDenormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;

final readonly class UpdateStatusCoursClickService
{
    public function __construct(
        private CoursRepository $coursRepository,
        private StatusCoursRepository $statusCoursRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function update(): void
    {
        $coursArray = $this->coursRepository->getSuperLightAllCours();
        $archive = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ARCHIVE->value]);
        $enCours = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_COURS->value]);
        $passe = $this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::PASSE->value]);

        foreach ($coursArray as $cours) {
            if (!$cours instanceof Cours) {
                throw new \InvalidArgumentException('Un des éléments du tableau n\'est pas une instance de Cours.');
            }
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

            if ($dateCours > new \DateTime() && $dateCours < new \DateTime('+'.$cours->getDuree().' minutes') && $status === StatusCoursEnum::OUVERT->value) {
                $newStatus = $enCours;
            }

            if (null !== $newStatus) {
                $cours->setStatusCours($this->statusCoursRepository->find($newStatus->getId()));
                $coursDTOSerializer = new Serializer([new UpdateCoursDTOToCoursDenormalizer($this->statusCoursRepository, $this->coursRepository)]);
                $cours = $coursDTOSerializer->denormalize($cours, Cours::class);
                $this->em->persist($cours);
                $this->em->flush();
            }
        }
    }
}
