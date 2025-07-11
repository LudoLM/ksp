<?php

namespace App\Controller\Api;

use App\DTO\CreateUserDTO;
use App\DTO\EditUserDTO;
use App\DTO\ResetPasswordDTO;
use App\Entity\User;
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

class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $JWTManager,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ForgotPasswordService $forgotPasswordService,
        private readonly CreateOrEditUserService $createOrEditUserService,
    ) {
    }

    #[Route(path: 'api/register', name: 'api_app_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload]
        CreateUserDTO $createUserDTO,
    ): JsonResponse {
        try {
            $token = $this->createOrEditUserService->createOrEditUser(null, $createUserDTO);

            return new JsonResponse([
                'message' => 'Utilisateur créé',
                'token' => $token,
            ]);
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            // Tente de décoder le message (au cas où c’est du JSON)
            $decoded = json_decode($message, true);
            // Si le message est un JSON valide avec une clé 'errors', renvoie-le directement
            if (array_key_exists('errors', $decoded)) {
                return new JsonResponse($decoded, Response::HTTP_BAD_REQUEST);
            }

            // Sinon, fallback classique
            return new JsonResponse([
                'type' => 'error',
                'message' => $message,
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route(path: 'api/editUser', name: 'api_app_edit_profile', methods: ['POST'])]
    public function editProfile(
        #[MapRequestPayload]
        EditUserDTO $editUserDTO,
    ): JsonResponse {
        $user = $this->getUser();
        try {
            $token = $this->createOrEditUserService->createOrEditUser($user, $editUserDTO);

            return new JsonResponse([
                'message' => 'Utilisateur modifié',
                'token' => $token,
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'type' => 'error',
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route(path: 'api/forgot-password', name: 'api_app_forget_password')]
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

    #[Route(path: 'api/reset-password', name: 'api_app_reset_password')]
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
