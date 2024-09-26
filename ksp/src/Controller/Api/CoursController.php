<?php

namespace App\Controller\Api;


use App\DTO\CreateCoursDTO;
use App\Entity\Cours;
use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Service\UpdateStatusCours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
        private readonly TypeCoursRepository $typeCoursRepository,
        private readonly UpdateStatusCours $updateStatusCours
    )
    {

    }

    #[Route('getCours', name: 'cours_index', methods: ['GET'])]
    public function coursIndex(Request $request): JsonResponse
    {
        $role = $request->headers->get("X-ACCESS-TOKEN");
        if ($role === 'ROLE_ADMIN') {
            $cours = $this->coursRepository->findAllSortByDate();
        } else {
            $cours = $this->coursRepository->findAllSortByDateForUsers();
        }
        $cours = $this->updateStatusCours->updateStatusCours($cours);
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
            ['response' => true, 'statusChange' => $statusChange, "usersCount" => $usersCount], 200);
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
        return new JsonResponse(['response' => true, 'statusChange' => $statusChange, 'usersCount' => $usersCount], 200);
    }

    // Add route for create new cours
    #[Route('cours/create', name: 'cours_create', methods: ['POST'])]
    #IsGranted("ROLE_ADMIN")
    public function createCours(
        Request $request,
        #[MapRequestPayload(
            serializationContext: [
                'groups' => ['cours:create']
            ]
        )]
        CreateCoursDTO $coursDTO
    ) : JsonResponse
    {
        $newCours = new Cours();
        $newCours->setDuree($coursDTO->dureeCours);
        $newCours->setDateCours($coursDTO->dateCours);
        $newCours->setDescription($coursDTO->description);
        $newCours->setTarif($coursDTO->tarif);
        $newCours->setNbInscriptionMax($coursDTO->nbInscriptionMax);
        $newCours->setTypeCours($this->typeCoursRepository->find($coursDTO->typeCours));
        $newCours->setDateLimiteInscription($coursDTO->dateLimiteInscription);
        $newCours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_CREATION->value]));


        $this->em->persist($newCours);
        $this->em->flush();

        return new JsonResponse(['response' => true], 200);
    }


    //Delete route for delete cours
    #[Route('cours/delete/{id}', name: 'cours_delete', methods: ['DELETE'])]
    public function deleteCours(Cours $cours): JsonResponse
    {
        $this->em->remove($cours);
        $this->em->flush();

        return new JsonResponse(['response' => true], 200);
    }
}