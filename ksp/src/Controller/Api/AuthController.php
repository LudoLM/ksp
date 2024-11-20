<?php

namespace App\Controller\Api;

use App\DTO\CreateUserDTO;
use App\Entity\Cours;
use App\Entity\User;
use App\Serializer\CreateUserDTOToUserDenormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Serializer\Serializer;

#[Route(path: 'api/', name: 'api_')]
class AuthController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $JWTManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly CreateUserDTOToUserDenormalizer $createUserDTOToUserDenormalizer
    ) {

    }

    #[Route(path: 'register', name: 'app_register')]
    public function register(
        #[MapRequestPayload] CreateUserDTO $createUserDTO
    ): JsonResponse {

        $user = $this->createUserDTOToUserDenormalizer->denormalize($createUserDTO, User::class);

        $this->em->persist($user);
        $this->em->flush();

        $token = $this->JWTManager->create($user);
        $authenticatedToken = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($authenticatedToken);


        return new JsonResponse([
            'message' => 'Utilisateur créé',
            'token' => $token
        ]);
    }

}