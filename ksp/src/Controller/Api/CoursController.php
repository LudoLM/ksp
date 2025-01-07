<?php

namespace App\Controller\Api;


use App\DTO\CreateCoursDTO;
use App\Entity\Cours;
use App\Entity\User;
use App\Entity\UsersCours;
use App\Enum\StatusCoursEnum;
use App\Event\CancelCoursEvent;
use App\Event\DesistementEvent;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Repository\UserRepository;
use App\Serializer\CreateCoursDTOToCoursDenormalizer;
use App\Service\UpdateStatusCoursService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
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
        private readonly EventDispatcherInterface $dispatcher,
        private readonly UserRepository $userRepository
    )
    {

    }
    #[Route('getCours', name: 'cours_index', methods: ['GET'])]
    public function coursIndex(Request $request): JsonResponse
    {
        $isAdminPath = $request->query->get('isAdminPath') === 'true';
        $currentPage = (int)($request->query->get('page', 1)); // Valeur par défaut 1
        $maxPerPage = (int)($request->query->get('maxPerPage', 10)); // Valeur par défaut 10

        $typeCoursId = $request->query->get('typeCours') ==="null" ? null : $request->query->get('typeCours') ?? null;
        $dateCoursStr = $request->query->get('dateCours')  ==="null" ? null : $request->query->get('dateCours') ?? null;
        $statusCoursId = $request->query->get('statusCours') ==="null" ? null : $request->query->get('statusCours') ?? null;


        // Récupérer l'entité TypeCours si `typeCours` est fourni
        $typeCours = null;
        if ($typeCoursId) {
            $typeCours = $this->typeCoursRepository->findOneBy(['id' => $typeCoursId]);
        }

        // Convertir la chaîne de date en \DateTime si `dateCours` est fourni
        $dateCours = null;
        if ($dateCoursStr) {
            try {
                $dateCours = new \DateTime($dateCoursStr);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid date format'], 400);
            }
        }

        $statusCours = null;
        if ($statusCoursId) {
            $statusCours = $this->statusCoursRepository->findOneBy(['id' => $statusCoursId]);
        }


        // Appeler le repository avec la pagination et les filtres

        if($isAdminPath) {
            $coursPaginator = $this->coursRepository->findAllSortByDate($currentPage, $maxPerPage, $typeCours, $dateCours, $statusCours);
        } else {
            $coursPaginator = $this->coursRepository->findAllSortByDateForUsers($currentPage, $maxPerPage, $typeCours, $dateCours, $statusCours);
        }

        // Récupérer les cours
        $cours = iterator_to_array($coursPaginator);


        // Calculer les métadonnées de pagination
        $totalItems = count($coursPaginator);
        $totalPages = ceil($totalItems / $maxPerPage);

        // Préparer la réponse
        $responseData = [
            'data' => $cours,
            'pagination' => [
                'currentPage' => $currentPage,
                'maxPerPage' => $maxPerPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
            ],
        ];


        $responseData = $this->serializer->serialize($responseData, 'json', ['groups' => 'cours:index']);

        return new JsonResponse($responseData, 200);
    }



    #[Route('getCours/{id}', name: 'cours_detail', methods: ['GET'])]
    public function coursFiltered(int $id): JsonResponse
    {
        $cours = $this->coursRepository->find($id);
        $jsonCours = $this->serializer->serialize($cours, 'json', ['groups' => 'cours:detail']);

        return new JsonResponse($jsonCours);
    }

    #[Route('addUser', name: 'cours_add_user', methods: ['POST'])]
    public function addUserToCours(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $data['userId'] === null ? $this->getUser() : $this->userRepository->find($data['userId']);
        $cours = $this->coursRepository->find($data['coursId']);
        $isAttente = $data['isAttente'];

//      Calcul du nombre de participants au cours
        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();}));
        $statusChange = $cours->getStatusCours()->getLibelle();

//      Si l'admin ajoute un extra
        if($user !== $this->getUser()) {
            $usersCours = new UsersCours();
            $usersCours->setUser($user);
            $usersCours->setCreatedAt(new \DateTimeImmutable());
            $usersCours->setEnAttente(false);
            $cours->addUsersCours($usersCours);
            $user->setNombreCours($user->getNombreCours() - 1);
            $isFull = $usersCount + 1 >= $cours->getNbInscriptionMax();
            if ($isFull) {
                $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::COMPLET->value]));
                $statusChange = StatusCoursEnum::COMPLET->value;
            }
            $this->em->persist($cours);
            $this->em->flush();
            return new JsonResponse(['success' => true, 'response' => $user->getPrenom() . " " . $user->getNom() ." a bien été ajouté au cours", 'statusChange' => $statusChange, "usersCount" => $usersCount], 200);
        }

//      Si le cours n'est pas en attente mais complet
        if ($usersCount >= $cours->getNbInscriptionMax() && !$isAttente ) {
            return new JsonResponse([ 'success' => false, 'response' => "Le cours est complet", 'statusChange' => StatusCoursEnum::COMPLET->value, "usersCount" => $usersCount], 200);
        }

//      Si le cours n'est pas complet, je vérifie si l'utilisateur est déjà inscrit ou en attente
        $usersCoursFiltered = array_filter($cours->getUsersCours()->toArray(), function ($usersCours) use ($user) { return $usersCours->getUser() === $user;});

//      Si l'utilisateur est déjà en attente, je le passe en inscrit
        if(count($usersCoursFiltered) > 0) {
            $usersCours = array_values($usersCoursFiltered)[0];
            $usersCours->setEnAttente(false);
            $usersCours->setCreatedAt(new \DateTimeImmutable());
        }
//      Si l'utilisateur n'est pas inscrit, je l'ajoute à la liste des participants
        else{
            $usersCours = new UsersCours();
            $usersCours->setUser($user);
            $usersCours->setCreatedAt(new \DateTimeImmutable());
            $usersCours->setEnAttente($isAttente);
            $cours->addUsersCours($usersCours);
        }

//      Si le cours est complet, je change le statut du cours
        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();}));
        $isFull = $usersCount >= $cours->getNbInscriptionMax();
        if ($isFull) {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::COMPLET->value]));
            $statusChange = StatusCoursEnum::COMPLET->value;
        }

//       Si le cours n'est pas en attente alors on décrémente le nombre de cours de l'utilisateur
        if (!$isAttente) {
            $this->getUser()->setNombreCours($this->getUser()->getNombreCours() - 1);
        }


//      Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();


//      Retourne une réponse JSON pour indiquer que l'utilisateur a été ajouté avec succès
        return new JsonResponse(['success' => true, 'response' => !$isAttente ? "Vous êtes bien inscrit au cours" : "Vous êtes sur la liste d'attente", 'statusChange' => $statusChange, "usersCount" => $usersCount], 200);
    }


    #[Route('removeUser/{id}/{isAttente}', name: 'cours_remove_user')]
    public function removeUserFromCours(Cours $cours, string $isAttente): JsonResponse
    {
        $user = $this->getUser();
        $isAttente = $isAttente === 'true';
        $statusChange = $cours->getStatusCours()->getLibelle();
        // Suppression de l'utilisateur du cours
        foreach ($cours->getUsersCours() as $usersCours) {
            if ($usersCours->getUser() === $user) {
                $cours->removeUsersCours($usersCours);
            }
        }
        if(!$isAttente) {
            $user->setNombreCours($user->getNombreCours() + 1);
        }

//        Si le cours est complet et qu'il y a de la place, je change le statut du cours et envoie un mail aux personnes en attente
        if(count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();})) < $cours->getNbInscriptionMax() && $cours->getStatusCours()->getLibelle() === StatusCoursEnum::COMPLET->value) {

            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));
            $statusChange = StatusCoursEnum::OUVERT->value;
            // Envoi d'un mail aux personnes en attente
            $eventCours = new DesistementEvent($cours);
            $this->dispatcher->dispatch($eventCours);
        }

        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();}));

//        // Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été supprimé avec succès
        return new JsonResponse(['success' => true, 'response' => !$isAttente ? 'Vous avez bien été supprimé du cours': 'Vous n\'êtes plus sur la liste d\'attente', 'statusChange' => $statusChange, 'usersCount' => $usersCount], 200);
    }

    // Add route for create new cours
    #[Route('cours/create', name: 'cours_create', methods: ['POST'])]
    #IsGranted("ROLE_ADMIN")
    public function createCours(
        #[MapRequestPayload(
            serializationContext: [
                'groups' => ['cours:create']
            ]
        )]
        CreateCoursDTO $coursDTO
    ) : JsonResponse
    {

        $coursDTOSeriliazer = new Serializer([new CreateCoursDTOToCoursDenormalizer($this->typeCoursRepository, $this->statusCoursRepository)]);
        $cours = $coursDTOSeriliazer->denormalize($coursDTO, Cours::class);
        $this->em->persist($cours);
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

    #[Route('cours/open/{id}', name: 'cours_open', methods: ['PUT'])]
    public function openCours(Cours $cours): JsonResponse
    {
        $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));

        $this->em->persist($cours);
        $this->em->flush();

        return new JsonResponse(['response' => true, 'statusChange' => StatusCoursEnum::OUVERT->value], 200);
    }

    #[Route('cours/cancel/{id}', name: 'cours_cancel', methods: ['PUT'])]
    public function cancelCours(Cours $cours): JsonResponse
    {

        $eventCours = new CancelCoursEvent($cours);
        $this->dispatcher->dispatch($eventCours);


        return new JsonResponse(['response' => true, 'statusChange' => StatusCoursEnum::ANNULE->value], 200);
    }

    #[Route('cours/edit/{id}', name: 'cours_update', methods: ['PUT'])]
    public function editCours(
        Cours $cours,
        #[MapRequestPayload(
            serializationContext: [
                'groups' => ['cours:create']
            ]
        )]
        CreateCoursDTO $coursDTO
    ) : JsonResponse
    {
        $coursDTOSeriliazer = new Serializer([new CreateCoursDTOToCoursDenormalizer($this->typeCoursRepository, $this->statusCoursRepository)]);
        $cours = $coursDTOSeriliazer->denormalize($coursDTO, Cours::class, context: ['object_to_populate' => $cours]);

        $this->em->persist($cours);
        $this->em->flush();

        return new JsonResponse(['response' => true], 200);
    }

}
