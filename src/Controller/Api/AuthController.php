<?php

namespace App\Controller\Api;

use App\DTO\CreateUserDTO;
use App\DTO\EditUserDTO;
use App\DTO\ResetPasswordDTO;
use App\Entity\User;
use App\Serializer\CreateUserDTOToUserDenormalizer;
use App\Serializer\ResetPasswordDTOToUserDenormalizer;
use App\Service\SendingEmail\ForgotPasswordService;
use App\Service\UserControllerService\CreateOrEditUserService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: 'api/', name: 'api_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $JWTManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly CreateUserDTOToUserDenormalizer $createUserDTOToUserDenormalizer,
        private readonly ForgotPasswordService $forgotPasswordService,
        private readonly CreateOrEditUserService $createOrEditUserService,
    ) {
    }

    #[Route(path: 'register', name: 'app_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload]
        CreateUserDTO $createUserDTO,
        ValidatorInterface $validator,
    ): JsonResponse {
        $user = $this->createUserDTOToUserDenormalizer->denormalize($createUserDTO, User::class);
        // Valider l'entité user
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

    #[Route(path: 'editUser', name: 'app_edit_profile', methods: ['POST'])]
    public function editProfile(
        #[MapRequestPayload]
        EditUserDTO $editUserDTO,
    ): JsonResponse {
        $user = $this->getUser();
        try {
            $this->createOrEditUserService->createOrEditUser($user, $editUserDTO);

            return new JsonResponse([
                'message' => 'Utilisateur modifié',
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'type' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route(path: 'forgot-password', name: 'app_forget_password')]
    public function forgotPassword(
        Request $request,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Vérifier que 'email' existe dans les données décodées
        if (!isset($data['email'])) {
            return new JsonResponse([
                'type' => 'error',
                'message' => "L'email est requis",
            ], Response::HTTP_BAD_REQUEST);
        }

        $emailReceived = $data['email'];

        return $this->forgotPasswordService->handleForgotPassword($emailReceived);
    }

    #[Route(path: 'reset-password', name: 'app_reset_password')]
    public function resetPassword(
        #[MapRequestPayload]
        ResetPasswordDTO $resetPasswordDTO,
        ResetPasswordDTOToUserDenormalizer $resetPasswordDTOToUserDenormalizer,
    ): JsonResponse {
        // Utilisez le denormalizer pour convertir le DTO en entité User;
        $user = $resetPasswordDTOToUserDenormalizer->denormalize($resetPasswordDTO, User::class);

        $this->em->persist($user);
        $this->em->flush();
        $token = $this->JWTManager->create($user);
        $authenticatedToken = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($authenticatedToken);

        return new JsonResponse([
            'message' => 'Mot de passe modifié',
            'token' => $token,
        ]);
    }
}
