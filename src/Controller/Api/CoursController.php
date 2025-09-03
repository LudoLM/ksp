<?php

namespace App\Controller\Api;

use App\DTO\CreateCoursDTO;
use App\Entity\Cours;
use App\Entity\User;
use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Repository\UserRepository;
use App\Serializer\CreateCoursDTOToCoursDenormalizer;
use App\Service\CoursControllerService\ActionsModifyOpenedCoursService;
use App\Service\CoursControllerService\CreateUsersCoursService;
use App\Service\CoursControllerService\FilteringCoursService;
use App\Service\CoursControllerService\UpdateStatusCoursService;
use App\Service\UpdateStatusCoursClickService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class CoursController extends AbstractController
{
    public function __construct(
        private readonly CoursRepository $coursRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly TypeCoursRepository $typeCoursRepository,
        private readonly UserRepository $userRepository,
        private readonly UpdateStatusCoursClickService $updateStatusCoursClickService,
        private readonly FilteringCoursService $filteringCoursService,
        private readonly CreateUsersCoursService $createUsersCoursService,
        private readonly ActionsModifyOpenedCoursService $actionsModifyOpenedCoursService,
        private readonly UpdateStatusCoursService $updateStatusCoursService,
    ) {
    }

    #[Route('api/getCoursCalendar', name: 'cours_calendar', methods: ['GET'])]
    #[Route('api/getCours', name: 'cours_index', methods: ['GET'])]
    #[Route('api/getOnlyNextCours', name: 'cours_next_cours', methods: ['GET'])]
    public function coursIndex(
        Request $request,
        #[MapQueryParameter] int $typeCoursId,
        #[MapQueryParameter] string $dateCoursStr,
        #[MapQueryParameter] int $statusCoursId,
        #[MapQueryParameter] bool $isOpenRequired = false,
    ): JsonResponse {
        $route = $request->attributes->get('_route');
        $user = $this->getUser();
        $isPrioritized = false;
        $isAdmin = false;
        if ($user instanceof User) {
            $isPrioritized = $user->isPrioritized();
            $isAdmin = 'ROLE_ADMIN' === $user->getRoles()[0];
        }
        try {
            $responseData = $this->filteringCoursService->filterCours($typeCoursId, $dateCoursStr, $statusCoursId, $route, $isOpenRequired, $isPrioritized, $isAdmin);

            // S'il n'y a pas de cours cette semaine, on renvoie un message d'erreur
            if ([] === $responseData) {
                throw new \Exception('Aucun cours cette semaine', 500);
            }
            if (array_key_exists('type', $responseData) && 'info_next_cours' === $responseData['type']) {
                return new JsonResponse($responseData, Response::HTTP_OK);
            }
            // Sinon, on sérialise les données
            $responseData = $this->serializer->serialize($responseData, 'json', ['groups' => 'cours:index']);

            return new JsonResponse($responseData, Response::HTTP_OK, json : true);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        }
    }

    #[Route('api/getYearsRangeForCours', name: 'Year', methods: ['GET'])]
    public function getYearsRangeForCours(): JsonResponse
    {
        $yearsLimits = $this->coursRepository->getYearsRangeForCours();
        $yearsRange = range($yearsLimits['min'], $yearsLimits['max']);
        $jsonYearsRange = $this->serializer->serialize($yearsRange, 'json');

        return new JsonResponse($jsonYearsRange);
    }

    #[Route('api/getCours/{id}', name: 'cours_detail', methods: ['GET'])]
    public function coursFiltered(int $id): JsonResponse
    {
        $cours = $this->coursRepository->find($id);
        $jsonCours = $this->serializer->serialize($cours, 'json', ['groups' => 'cours:detail']);

        return new JsonResponse($jsonCours);
    }

    #[Route('api/addUser', name: 'cours_add_user', methods: ['POST'])]
    public function addUserToCours(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = null === $data['userId'] ? $this->getUser() : $this->userRepository->find($data['userId']);
        // Vérifiez que $user est une instance de la classe user
        if (!$user instanceof User) {
            throw new \Exception('Type de l\'utilisateur invalide');
        }

        $cours = $this->coursRepository->find($data['coursId']);
        $isOnWaitingList = $data['isOnWaitingList'];

        return $this->createUsersCoursService->createUsersCours($cours, $user, $isOnWaitingList);
    }

    #[Route('api/removeUser', name: 'cours_remove_user')]
    public function removeUserFromCours(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $cours = $this->coursRepository->find($data['coursId']);
        $isOnWaitingList = (bool) $data['isOnWaitingList'];
        $user = $this->getUser();

        // Vérifiez que $user est une instance de la classe utilisateur
        if (!$user instanceof User) {
            throw new \Exception('Type de l\'utilisateur invalide');
        }

        $statusChange = $cours->getStatusCours();
        // Suppression de l'utilisateur du cours
        foreach ($cours->getUsersCours() as $usersCours) {
            if ($usersCours->getUser() === $user) {
                $cours->removeUsersCours($usersCours);
            }
        }
        if (!$isOnWaitingList) {
            $user->setNombreCours($user->getNombreCours() + 1);
        }

        // Si le cours est complet et qu'il y a de la place, je change le statut du cours et envoie un mail aux personnes en attente
        if (count(array_filter($cours->getUsersCours()->toArray(), fn ($usersCours): bool => true !== $usersCours->isOnWaitingList())) < $cours->getNbInscriptionMax() && $cours->getStatusCours()->getLibelle() === StatusCoursEnum::COMPLET->value) {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));
            $statusChange = $cours->getStatusCours();
            // Envoi d'un mail aux personnes en attente
            /*$eventCours = new DesistementEvent($cours);
            $this->dispatcher->dispatch($eventCours);*/
        }

        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), fn ($usersCours): bool => true !== $usersCours->isOnWaitingList()));

        //        // Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été supprimé avec succès
        return new JsonResponse([
            'success' => true,
            'message' => $isOnWaitingList ? 'Vous n\'êtes plus sur la liste d\'attente' : 'Vous avez bien été supprimé du cours',
            'statusChange' => $this->serializer->serialize($statusChange, 'json', ['groups' => 'cours:detail']), 'usersCount' => $usersCount,
            'userCoursQuantity' => $user->getNombreCours(),
        ], Response::HTTP_OK);
    }

    // Add route for create new cours
    #[Route('api/cours/create', name: 'cours_create', methods: ['POST'])]
    // IsGranted("ROLE_ADMIN")
    public function createCours(
        #[MapRequestPayload(
            serializationContext: [
            ]
        )]
        CreateCoursDTO $coursDTO,
    ): JsonResponse {
        $coursDTOSerializer = new Serializer([new CreateCoursDTOToCoursDenormalizer($this->typeCoursRepository, $this->statusCoursRepository)]);
        $cours = $coursDTOSerializer->denormalize($coursDTO, Cours::class);
        $this->em->persist($cours);
        $this->em->flush();

        return new JsonResponse(['response' => true], Response::HTTP_OK);
    }

    // Delete route for delete cours
    #[Route('api/cours/delete/{id}', name: 'cours_delete', methods: ['DELETE'])]
    public function deleteCours(Cours $cours): JsonResponse
    {
        $this->em->remove($cours);
        $this->em->flush();

        return new JsonResponse(['success' => true, 'type' => 'success', 'message' => 'Le cours a bien été effacé'], Response::HTTP_OK);
    }

    #[Route('api/cours/open/{id}', name: 'cours_open', methods: ['PUT'])]
    public function openCours(Cours $cours): JsonResponse
    {
        try {
            $this->updateStatusCoursService->prepareAndLaunchCours($cours);
            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'type' => 'success',
                'message' => 'Le cours est maintenant ouvert aux inscriptions',
                'statusChange' => $this->serializer->serialize($cours->getStatusCours(), 'json', ['groups' => 'cours:detail']),
                Response::HTTP_OK]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    #[Route('api/cours/cancel/{id}', name: 'cours_cancel', methods: ['PUT'])]
    public function cancelCours(Cours $cours): JsonResponse
    {
        try {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ANNULE->value]));
            $this->em->persist($cours);
            /*foreach ($cours->getUsersCours() as $usersCours){

                $messageBus->dispatch(new SendCancelEmailMessage($usersCours->getId(), $this->getUser()->getId() ));
            }*/
            $this->em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Le cours a été annulé',
                'statusChange' => $this->serializer->serialize($cours->getStatusCours(), 'json', ['groups' => 'cours:detail']), Response::HTTP_OK,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('api/cours/edit/{id}', name: 'cours_update', methods: ['PUT'])]
    public function editCours(
        #[MapRequestPayload]
        CreateCoursDTO $coursDTO,
        Cours $cours,
    ): JsonResponse {
        $initialDate = $cours->getDateCours();
        $initalDuration = $cours->getDuree();

        $coursDTOSerializer = new Serializer([new CreateCoursDTOToCoursDenormalizer($this->typeCoursRepository, $this->statusCoursRepository)]);
        $cours = $coursDTOSerializer->denormalize($coursDTO, Cours::class, context: ['object_to_populate' => $cours]);
        $this->em->persist($cours);
        $this->em->flush();

        if (StatusCoursEnum::OUVERT->value === $cours->getStatusCours()->getLibelle()) {
            try {
                $this->actionsModifyOpenedCoursService->handle($cours, $initalDuration, $initialDate);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }
        }

        return new JsonResponse(['response' => true], Response::HTTP_OK);
    }

    #[Route('api/getCoursFilling', name: 'cours_filling', methods: ['GET'])]
    public function getCoursFilling(): JsonResponse
    {
        $coursFilling = $this->coursRepository->getCoursFilling();
        $jsonCoursFillings = $this->serializer->serialize($coursFilling, 'json', ['groups' => 'cours_filling:index']);

        return new JsonResponse($jsonCoursFillings, Response::HTTP_OK);
    }

    #[Route('api/updateCoursClick', name: 'updateCoursClick', methods: ['GET'])]
    public function updateCoursClick(): JsonResponse
    {
        $this->updateStatusCoursClickService->update();

        return new JsonResponse(['success' => true, 'message' => 'Les statuts des cours ont bien été mis à jour'], Response::HTTP_OK);
    }

    #[Route('api/removeUsers/{id}', name: 'remove_users_cours', methods: ['POST'])]
    public function removeUsersFromCours(
        Cours $cours,
        Request $request,
    ): JsonResponse {
        $participants = json_decode($request->getContent(), true)['usersChecked'];
        $statusChange = $cours->getStatusCours();
        foreach ($cours->getUsersCours() as $usersCours) {
            if (in_array($usersCours->getUser()->getId(), $participants, true)) {
                $cours->removeUsersCours($usersCours);
                $usersCours->getUser()->setNombreCours($usersCours->getUser()->getNombreCours() + 1);
            }
        }
        //        Si le cours est complet et qu'il y a de la place, je change le statut du cours et envoie un mail aux personnes en attente
        if (count(array_filter($cours->getUsersCours()->toArray(), fn ($usersCours): bool => true !== $usersCours->isOnWaitingList())) < $cours->getNbInscriptionMax() && $cours->getStatusCours()->getLibelle() === StatusCoursEnum::COMPLET->value) {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));
            $statusChange = $cours->getStatusCours();
            // Envoi d'un mail aux personnes en attente
            /*$eventCours = new DesistementEvent($cours);
            $this->dispatcher->dispatch($eventCours);*/
        }
        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), fn ($usersCours): bool => true !== $usersCours->isOnWaitingList()));

        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été supprimé avec succès
        return new JsonResponse([
            'success' => true,
            'message' => 'Les participants ont bien été supprimés du cours',
            'statusChange' => $this->serializer->serialize($statusChange, 'json', ['groups' => 'cours:detail']),
            'usersCount' => $usersCount], Response::HTTP_OK);
    }
}
