<?php

namespace App\Controller\Api;

use App\DTO\CreateUserDTO;
use App\DTO\EditUserDTO;
use App\DTO\ResetPasswordDTO;
use App\Entity\User;
use App\Serializer\ResetPasswordDTOToUserDenormalizer;
use App\Service\SendingEmail\ForgotPasswordService;
use App\Service\UserControllerService\CreateOrEditUserService;
use App\Service\UserControllerService\FetchUserService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $JWTManager,
        private readonly ForgotPasswordService $forgotPasswordService,
        private readonly CreateOrEditUserService $createOrEditUserService,
        private readonly RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
        private readonly FetchUserService $fetchUserService,
    ) {
    }

    #[Route(path: 'api/register', name: 'api_app_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload]
        CreateUserDTO $createUserDTO,
    ): JsonResponse {
        try {
            $user = $this->createOrEditUserService->createOrEditUser(null, $createUserDTO);
            $this->em->persist($user);
            $this->em->flush();
            [$jwtCookie, $refreshCookie] = $this->createTokens($user);

            // Crée la réponse avec un message JSON
            $response = new JsonResponse(json_encode([
                'message' => 'Utilisateur créé',
            ]), Response::HTTP_CREATED);

            $response->headers->setCookie($jwtCookie);
            $response->headers->setCookie($refreshCookie);

            return $response;
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

    #[Route(path: 'api/editUser/{id<\d+>?}', name: 'api_app_edit_profile', methods: ['POST'])]
    public function editProfile(
        #[MapRequestPayload]
        EditUserDTO $editUserDTO,
        ?int $id = null,
    ): JsonResponse {
        $user = $this->fetchUserService->fetchUser($id);
        try {
            $this->createOrEditUserService->createOrEditUser($user, $editUserDTO);
            $this->em->persist($user);
            $this->em->flush();

            return new JsonResponse([
                'message' => 'Utilisateur modifié',
            ], Response::HTTP_OK);
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

        [$jwtCookie, $refreshCookie] = $this->createTokens($user);

        // Crée la réponse avec un message JSON
        $response = new JsonResponse(json_encode([
            'message' => 'Mot de passe modifié',
        ]), Response::HTTP_CREATED);

        $response->headers->setCookie($jwtCookie);
        $response->headers->setCookie($refreshCookie);

        return $response;
    }

    public function createTokens(
        User $user,
    ): array {
        // Génère le bearer token JWT
        $token = $this->JWTManager->create($user);

        // Génère le refresh token
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 604800); // 7 jours de TTL
        $this->refreshTokenManager->save($refreshToken);

        // Crée les cookies httpOnly pour les tokens et les ajoute à la réponse
        $jwtCookie = Cookie::create('BEARER', $token)
            ->withHttpOnly()
            ->withExpires(time() + 3600); // 1 heure d'expiration

        $refreshCookie = Cookie::create('refresh_token', $refreshToken->getRefreshToken())
            ->withHttpOnly()
            ->withExpires(time() + 604800); // 7 jours d'expiration

        return [$jwtCookie, $refreshCookie];
    }
}
