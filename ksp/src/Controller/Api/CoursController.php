<?php

namespace App\Controller\Api;


use App\DTO\CreateCoursDTO;
use App\Entity\Cours;
use App\Entity\UsersCours;
use App\Enum\StatusCoursEnum;
use App\Event\CancelCoursEvent;
use App\Event\DesistementEvent;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Serializer\CreateCoursDTOToCoursDenormalizer;
use App\Service\UpdateStatusCoursService;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

#[Route(path: "api/", name:"api")]
class CoursController extends AbstractController
{

    public function __construct(
        private readonly CoursRepository $coursRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly TypeCoursRepository $typeCoursRepository,
        private readonly UpdateStatusCoursService $updateStatusCours,
        private readonly EventDispatcherInterface $dispatcher
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

    #[Route('addUser/{id}/{isAttente?}', name: 'cours_add_user', methods: ['POST'])]
    public function addUserToCours(Cours $cours, ?string $isAttente): JsonResponse
    {
        $user = $this->getUser();
        $isAttente = $isAttente === 'true';
        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();}));
        $statusChange = $cours->getStatusCours()->getLibelle();

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
        Request $request,
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