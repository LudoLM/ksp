<?php

namespace App\Controller\Api;

use App\DTO\CreateUserDTO;
use App\Entity\User;
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

#[Route(path: 'api/', name: 'api_')]
class AuthController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly JWTTokenManagerInterface $JWTManager,
        private readonly TokenStorageInterface $tokenStorage
    )
    {

    }

    #[Route(path: 'register', name: 'app_register')]
    public function register(
        Security $security,
        Request $request,
        #[MapRequestPayload] CreateUserDTO $createUserDTO,

    ): JsonResponse
    {

        $user = new User();
        $user->setPrenom($createUserDTO->prenom);
        $user->setNom($createUserDTO->nom);
        $user->setEmail($createUserDTO->email);

        // Hashage du mot de passe
        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $user,
            $createUserDTO->password
        );
        $user->setPassword($hashedPassword);
        $user->setTelephone($createUserDTO->telephone);
        $user->setAdresse($createUserDTO->adresse);
        $user->setCodePostal($createUserDTO->cp);
        $user->setCommune($createUserDTO->commune);
        $user->setRoles(['ROLE_USER']);
        $user->setNombreCours(0);


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