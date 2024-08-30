<?php

namespace App\Controller\Api;


use App\Entity\Cours;
use App\Repository\CoursRepository;
use App\Service\DefaultContext;
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
        private readonly EntityManagerInterface $em
    )
    {

    }

    #[Route('getCours', name: 'cours_index', methods: ['GET'])]
    public function coursIndex(Request $request): JsonResponse
    {
        $cours = $this->coursRepository->findAllSortByDate();
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

        // Ajout de l'utilisateur au cours
        $cours->addUser($user);

        // Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été ajouté avec succès
        return new JsonResponse(['message' => 'Utilisateur ajouté'], 200);
    }

    #[Route('removeUser/{id}', name: 'cours_remove_user')]
    public function removeUserFromCours(Cours $cours): JsonResponse
    {
        $user = $this->getUser();
        // Suppression de l'utilisateur du cours
        $cours->removeUser($user);

        // Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été supprimé avec succès
        return new JsonResponse(['message' => 'Utilisateur supprimé'], 200);
    }



}