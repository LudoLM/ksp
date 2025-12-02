<?php

namespace App\Controller\Api;

use App\DTO\UpdateCoursCountDTO;
use App\Entity\Cours;
use App\Entity\User;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use App\Service\UserControllerService\FetchUserService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'User')]
class UserController extends AbstractController
{
    public const LIMIT_USERS_PER_PAGE = 15;
    public const LIMIT_COURS_PER_PAGE = 10;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly NormalizerInterface $normalizer,
        private readonly CoursRepository $coursRepository,
        private readonly UserRepository $userRepository,
        private readonly FetchUserService $fetchUserService,
    ) {
    }

    #[Route('/api/admin/users', name: 'api_all_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Seuls les administrateurs peuvent avoir accès à tous les utilisateurs.')]
    public function getAllUsers(
        #[MapQueryParameter(name: 'page')] int $page,
        #[MapQueryParameter(name: 'searchUser')] string $searchUser = '',
    ): JsonResponse {
        $paginator = $this->userRepository->paginateUsers($page, self::LIMIT_USERS_PER_PAGE, $searchUser);
        $totalItems = $paginator->count();

        $usersArray = $this->normalizer->normalize(iterator_to_array($paginator->getIterator()), 'json', ['groups' => 'user:detail']);
        $totalPages = ceil($totalItems / self::LIMIT_USERS_PER_PAGE);

        $responsePayload = [
            'metadata' => [
                'total_items' => $totalItems,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
            'data' => $usersArray,
        ];

        return new JsonResponse($responsePayload, Response::HTTP_OK, []);
    }

    #[Route('/api/user/{id<\d+>?}', name: 'api_user', methods: ['GET'])]
    public function getUserData(?int $id = null): JsonResponse
    {
        try {
            $user = $this->fetchUserService->fetchUser($id);
        } catch (NotFoundHttpException|AccessDeniedException $e) {
            return new JsonResponse(
                ['message' => $e->getMessage()],
                $e instanceof NotFoundHttpException ? Response::HTTP_NOT_FOUND : Response::HTTP_FORBIDDEN
            );
        }

        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'user:detail']);

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/api/delete-user/{id<\d+>}', name: 'api_delete_user', methods: ['DELETE'])]
    public function deleteUser(?int $id = null): JsonResponse
    {
        try {
            $user = $this->fetchUserService->fetchUser($id);
            $this->userRepository->remove($user, true);

            return new JsonResponse(['message' => 'Utilisateur supprimé'], Response::HTTP_OK);
        } catch (NotFoundHttpException|AccessDeniedException $e) {
            return new JsonResponse(
                ['message' => $e->getMessage()],
                $e instanceof NotFoundHttpException ? Response::HTTP_NOT_FOUND : Response::HTTP_FORBIDDEN
            );
        } catch (\Exception $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/user/{id<\d+>?}/payments-history', name: 'api_user_history', methods: ['GET'])]
    public function getUserPaymentsHistory(?int $id): JsonResponse
    {
        try {
            $user = $this->fetchUserService->fetchUser($id);
        } catch (NotFoundHttpException|AccessDeniedException $e) {
            return new JsonResponse(
                ['message' => $e->getMessage()],
                $e instanceof NotFoundHttpException ? Response::HTTP_NOT_FOUND : Response::HTTP_FORBIDDEN
            );
        }

        $payments = $this->serializer->serialize($user, 'json', ['groups' => 'user:payments']);

        return new JsonResponse($payments, Response::HTTP_OK, [], true);
    }

    #[Route('/api/user/{id<\d+>?}/cours-history', name: 'api_user_cours_history', methods: ['GET'])]
    public function getUserCoursHistory(
        ?int $id = null,
        #[MapQueryParameter] int $page = 1,
    ): JsonResponse {
        try {
            $user = $this->fetchUserService->fetchUser($id);
        } catch (NotFoundHttpException|AccessDeniedException $e) {
            return new JsonResponse(
                ['message' => $e->getMessage()],
                $e instanceof NotFoundHttpException ? Response::HTTP_NOT_FOUND : Response::HTTP_FORBIDDEN
            );
        }
        $paginator = $this->coursRepository->paginateUserCours($user, $page, self::LIMIT_COURS_PER_PAGE);
        $totalItems = $paginator->count();

        $userCoursArray = $this->normalizer->normalize(iterator_to_array($paginator->getIterator()), 'json', ['groups' => 'user:profile']);
        $totalPages = ceil($totalItems / self::LIMIT_COURS_PER_PAGE);

        $responsePayload = [
            'metadata' => [
                'total_items' => $totalItems,
                'current_page' => $page,
                'total_pages' => $totalPages,
            ],
            'data' => $userCoursArray,
        ];

        return new JsonResponse($responsePayload, Response::HTTP_OK, []);
    }

    #[Route('/api/admin/users-not-in-cours/{cours}', name: 'api_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'Seuls les administrateurs peuvent avoir accès à tous les utilisateurs.')]
    public function getUsersData(UserRepository $userRepository, Cours $cours): JsonResponse
    {
        $users = $userRepository->getLightUsersAll($cours);
        $jsonUsers = $this->serializer->serialize($users, 'json');

        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/admin/users/reset-counters', name: 'api_reset_all_users_counter_cours', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Seuls les administrateurs peuvent reset tous les cours')]
    public function resetAllUserscounterCours(): JsonResponse
    {
        try {
            // Appeler la méthode de mise à jour en masse
            $this->userRepository->resetAllUsersCounterCours();

            // Retourner le nombre d'utilisateurs affectés
            return new JsonResponse([
                'message' => 'Le compteur de cours a été réinitialisé pour tous les utilisateurs.',
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/admin/user/{id}/cours-count  ', name: 'api_update_all_users_cours_count', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Seuls les administrateurs peuvent modifier le compte de cours.')]
    public function updateUserCoursCount(
        #[MapRequestPayload] UpdateCoursCountDTO $updateCoursCountDTO,
        User $user,
    ): JsonResponse {
        try {
            $user->setNombreCours($updateCoursCountDTO->nombreCours);
            $this->userRepository->save($user, true);

            return new JsonResponse([
                'message' => 'Le nombre de cours de l\'utilisateur a été mis à jour.',
            ], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
