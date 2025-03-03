<?php
namespace App\Controller\Api;

use App\DTO\AddUserToCoursDTO;
use App\DTO\AddUserToCoursDTOToUserCours;
use App\DTO\CreateCoursDTO;
use App\Entity\Cours;
use App\Entity\UsersCours;
use App\Enum\StatusCoursEnum;
use App\Manager\UsersCoursManager;
use App\Message\UpdateStatusCoursMessage;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Repository\UserRepository;
use App\Serializer\AddUserToCoursDTOToUsersCoursDenormalizer;
use App\Serializer\CreateCoursDTOToCoursDenormalizer;
use App\Service\CoursControllerService\AddUserTimeCheckerService;
use App\Service\CoursControllerService\CountUsersInCoursService;
use App\Service\CoursControllerService\CreateUsersCoursService;
use App\Service\CoursControllerService\FilteringCoursService;
use App\Service\CoursControllerService\SubscribeExtraUserService;
use App\Service\UpdateStatusCoursClickService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;


#[Route(path: "api/", name:"api_")]
class CoursController extends AbstractController
{

    public function __construct(
        private readonly CoursRepository               $coursRepository,
        private readonly SerializerInterface           $serializer,
        private readonly EntityManagerInterface        $em,
        private readonly StatusCoursRepository         $statusCoursRepository,
        private readonly TypeCoursRepository           $typeCoursRepository,
        private readonly EventDispatcherInterface      $dispatcher,
        private readonly UserRepository                $userRepository,
        private readonly MessageBusInterface           $messageBus,
        private readonly UpdateStatusCoursClickService $updateStatusCoursClickService,
        private readonly FilteringCoursService         $filteringCoursService,
        private readonly AddUserTimeCheckerService     $addUserTimeCheckerService,
        private readonly CountUsersInCoursService      $countUsersInCoursService,
        private readonly UsersCoursManager             $usersCoursManager, private readonly CreateUsersCoursService $createUsersCoursService,
    )
    {
    }
    #[Route('getCoursCalendar', name: 'cours_calendar', methods: ['GET'])]
    #[Route('getCours', name: 'cours_index', methods: ['GET'])]
    public function coursIndex(
        Request $request,
        #[MapQueryParameter] int $currentPage,
        #[MapQueryParameter] int $maxPerPage,
        #[MapQueryParameter] int $typeCoursId,
        #[MapQueryParameter] string $dateCoursStr,
        #[MapQueryParameter] int $statusCoursId

    ): JsonResponse {

        //Si apres getPath c'est "/admin" alors isAdminPath = true
        $isAdminPath = str_starts_with($request->headers->get('referer'),$request->getSchemeAndHttpHost() . '/admin');
        $route = $request->attributes->get('_route');
        try {
            $responseData = $this->filteringCoursService->filterCours($currentPage, $maxPerPage, $typeCoursId, $dateCoursStr, $statusCoursId, $route, $isAdminPath);
            $responseData = $this->serializer->serialize($responseData, 'json', ['groups' => 'cours:index']);
            return new JsonResponse($responseData, 200);
        }catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], $e->getCode());
        }
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

        return $this->createUsersCoursService->createUsersCours($cours, $user, $isAttente);
    }


    #[Route('removeUser/{id}/{isAttente}', name: 'cours_remove_user')]
    public function removeUserFromCours(Cours $cours, string $isAttente): JsonResponse
    {
        $user = $this->getUser();
        $isAttente = $isAttente === 'true';
        $statusChange = $cours->getStatusCours();
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
            $statusChange = $cours->getStatusCours();
            // Envoi d'un mail aux personnes en attente
            /*$eventCours = new DesistementEvent($cours);
            $this->dispatcher->dispatch($eventCours);*/
        }

        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();}));

//        // Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été supprimé avec succès
        return new JsonResponse(['success' => true, 'message' => !$isAttente ? 'Vous avez bien été supprimé du cours': 'Vous n\'êtes plus sur la liste d\'attente', 'statusChange' => $this->serializer->serialize($statusChange, 'json', ['groups' => 'cours:detail']), 'usersCount' => $usersCount], 200);
    }

    // Add route for create new cours
    #[Route('cours/create', name: 'cours_create', methods: ['POST'])]
    #IsGranted("ROLE_ADMIN")
    public function createCours(
        #[MapRequestPayload(
            serializationContext: [

            ]
        )]
        CreateCoursDTO $coursDTO
    ) : JsonResponse
    {

        $coursDTOSerializer = new Serializer([new CreateCoursDTOToCoursDenormalizer($this->typeCoursRepository, $this->statusCoursRepository)]);;
        $cours = $coursDTOSerializer->denormalize($coursDTO, Cours::class);
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

        return new JsonResponse(['success' => true, 'message' => 'Le cours a bien été effacé'], 200);
    }

    #[Route('cours/open/{id}', name: 'cours_open', methods: ['PUT'])]
    public function openCours(Cours $cours): JsonResponse
    {
        //Si  la date du cours est passé, on ne peut pas ouvrir le cours
        if($cours->getDateCours()->getTimestamp() < time()) {
            return new JsonResponse(['success' => false, 'type'=> 'error', 'message' => 'Le date est déjà passé'], 400);
        }
        $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));
        $delay = $cours->getDateCours()->getTimestamp() - time();
        $this->messageBus->dispatch(
            new UpdateStatusCoursMessage(
                $cours->getId()),
                [ new DelayStamp($delay)]
        );
        $this->em->persist($cours);
        $this->em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Le cours est maintenant ouvert aux inscriptions', 'statusChange' => $cours->getStatusCours()], 200);
    }

    #[Route('cours/cancel/{id}', name: 'cours_cancel', methods: ['PUT'])]
    public function cancelCours(Cours $cours, MessageBusInterface $messageBus): JsonResponse
    {
        try {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ANNULE->value]));
            $this->em->persist($cours);
            /*foreach ($cours->getUsersCours() as $usersCours){

                $messageBus->dispatch(new SendCancelEmailMessage($usersCours->getId(), $this->getUser()->getId() ));
            }*/
            $this->em->flush();
            return new JsonResponse(['success' => true, 'message' => 'Le cours a été annulé', 'statusChange' => $cours->getStatusCours()], 200);
        }
        catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }


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
        $coursDTOSerializer = new Serializer([new CreateCoursDTOToCoursDenormalizer($this->typeCoursRepository, $this->statusCoursRepository)]);
        $cours = $coursDTOSerializer->denormalize($coursDTO, Cours::class, context: ['object_to_populate' => $cours]);

        $this->em->persist($cours);
        $this->em->flush();

        return new JsonResponse(['response' => true], 200);
    }

    #[Route('getCoursFilling', name: 'cours_filling', methods: ['GET'])]
    public function getCoursFilling(): JsonResponse
    {
       $coursFilling = $this->coursRepository->getCoursFilling();
       $jsonCoursFillings = $this->serializer->serialize($coursFilling, 'json', ['groups' => 'cours_filling:index']);
        return new JsonResponse($jsonCoursFillings, 200);
    }

    #[Route('updateCoursClick', name: 'updateCoursClick', methods: ['GET'])]
    public function updateCoursClick(): JsonResponse
    {
        $this->updateStatusCoursClickService->update();
        return new JsonResponse(['success' => true, 'message' => 'Les statuts des cours ont bien été mis à jour'], 200);
    }

    #[Route('removeUsers/{id}', name: 'remove_users_cours', methods: ['POST'])]
    public function removeUsersFromCours(
        Cours $cours,
        Request $request
    ): JsonResponse
    {
        $participants = json_decode($request->getContent(), true)['usersChecked'];
        $statusChange = $cours->getStatusCours();
        foreach ($cours->getUsersCours() as $usersCours) {
            if(in_array($usersCours->getUser()->getId(), $participants)) {
                $cours->removeUsersCours($usersCours);
                $usersCours->getUser()->setNombreCours($usersCours->getUser()->getNombreCours() + 1);
            }
        }
//        Si le cours est complet et qu'il y a de la place, je change le statut du cours et envoie un mail aux personnes en attente
        if(count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();})) < $cours->getNbInscriptionMax() && $cours->getStatusCours()->getLibelle() === StatusCoursEnum::COMPLET->value) {

            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));
            $statusChange = $cours->getStatusCours();
            // Envoi d'un mail aux personnes en attente
            /*$eventCours = new DesistementEvent($cours);
            $this->dispatcher->dispatch($eventCours);*/
        }
        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();}));

        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été supprimé avec succès
        return new JsonResponse([
            'success' => true,
            'message' => 'Les participants ont bien été supprimés du cours',
            'statusChange' => $this->serializer->serialize($statusChange, 'json', ['groups' => 'cours:detail']),
            'usersCount' => $usersCount], 200);
    }
}
