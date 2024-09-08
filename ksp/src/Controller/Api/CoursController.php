<?php

namespace App\Controller\Api;


use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Service\DefaultContext;
use App\Service\UpdateStatusCours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: "api/", name:"api")]
class CoursController extends AbstractController
{

    public function __construct(
        private readonly CoursRepository $coursRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly UpdateStatusCours $updateStatusCours
    )
    {

    }

    #[Route('getCours', name: 'cours_index', methods: ['GET'])]
    public function coursIndex(Request $request): JsonResponse
    {
        $cours = $this->updateStatusCours->updateStatusCours();
        $jsonCours = $this->serializer->serialize($cours, 'json', ['groups' => 'cours:index']);
        return new JsonResponse($jsonCours, 200, [], true);
    }


    #[Route('getCours/{id}', name: 'cours_detail', methods: ['GET'])]
    public function coursFiltered(int $id): JsonResponse
    {
        $cours = $this->coursRepository->find($id);
        $jsonCours = $this->serializer->serialize($cours, 'json', ['groups' => 'cours:detail']);

        return new JsonResponse($jsonCours);
    }

    #[Route('addUser/{id}', name: 'cours_add_user', methods: ['POST'])]
    public function addUserToCours(Cours $cours): JsonResponse
    {
        $user = $this->getUser();
        $statusChange = $cours->getStatusCours()->getLibelle();

        // Ajout de l'utilisateur au cours
        $cours->addUser($user);

        if (count($cours->getUsers()) >= $cours->getNbInscriptionMax()) {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::COMPLET->value]));
            $statusChange = StatusCoursEnum::COMPLET->value;
        }

        $usersCount = count($cours->getUsers());

        // Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été ajouté avec succès
        return new JsonResponse(
            ['reponse' => true, 'statusChange' => $statusChange, "usersCount" => $usersCount], 200);
    }


    #[Route('removeUser/{id}', name: 'cours_remove_user')]
    public function removeUserFromCours(Cours $cours): JsonResponse
    {
        $user = $this->getUser();
        $statusChange = $cours->getStatusCours()->getLibelle();
        // Suppression de l'utilisateur du cours
        $cours->removeUser($user);

        if (count($cours->getUsers()) < $cours->getNbInscriptionMax() && $cours->getStatusCours()->getLibelle() === StatusCoursEnum::COMPLET->value) {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));
            $statusChange = StatusCoursEnum::OUVERT->value;
        }

        $usersCount = count($cours->getUsers());

        // Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été supprimé avec succès
        return new JsonResponse(['reponse' => true, 'statusChange' => $statusChange, 'usersCount' => $usersCount], 200);
    }
}