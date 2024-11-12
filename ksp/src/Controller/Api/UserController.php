<?php

namespace App\Controller\Api;

use App\Entity\Cours;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController

{
    public function __construct(
        private readonly SerializerInterface $serializer
    )
    {
    }


    #[Route('/api/user', name: 'api_user', methods: ['GET'])]
    public function getUserData() : JsonResponse
    {
        $user = $this->getUser();
        if (!$user){
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }


        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'user:detail']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/api/usersNotInCours/{cours}', name: 'api_users', methods: ['GET'])]
    public function getUsersData(UserRepository $userRepository, Cours $cours) : JsonResponse
    {
        $users = $userRepository->getLightUsersAll($cours);
        $jsonUsers = $this->serializer->serialize($users, 'json', ['groups' => 'cours:index']);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

}