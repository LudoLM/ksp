<?php

namespace App\Controller\Api;

use App\DTO\CreateUserDTO;
use App\Entity\User;
use App\Serializer\CreateUserDTOToUserDenormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $JWTManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly CreateUserDTOToUserDenormalizer $createUserDTOToUserDenormalizer,
    ) {
    }

    #[Route(path: 'api/register', name: 'app_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload]
        CreateUserDTO $createUserDTO,
        ValidatorInterface $validator,
    ): JsonResponse {
        $user = $this->createUserDTOToUserDenormalizer->denormalize($createUserDTO, User::class);
        // Valide en cas de non duplication de l'email
        $violations = $validator->validate($user);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    $violation->getPropertyPath() => $violation->getMessage(),
                ];
            }

            return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($user);
        $this->em->flush();

        $token = $this->JWTManager->create($user);
        $authenticatedToken = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($authenticatedToken);

        return new JsonResponse([
            'message' => 'Utilisateur créé',
            'token' => $token,
        ]);
    }
}
