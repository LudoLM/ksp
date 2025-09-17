<?php

namespace App\Controller;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthentificationController extends AbstractController
{
    #[Route(path: '/login_check', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): JsonResponse
    {
        $user = $this->getUser();

        // Vérifiez que $user est une instance de la classe utilisateur
        if (!$user instanceof User) {
            throw new \Exception('Type de l\'utilisateur invalide');
        }

        return new JsonResponse([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(
        Request $request,
        RefreshTokenManagerInterface $refreshTokenManager,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        // 1. Récupérer le refresh token depuis le cookie
        $refreshTokenValue = $request->cookies->get('refresh_token');
        // 2. Si un refresh token existe, le supprimer en base de données
        if (null !== $refreshTokenValue) {
            $refreshToken = $refreshTokenManager->get($refreshTokenValue);
            if ($refreshToken instanceof RefreshToken) {
                $refreshTokenManager->delete($refreshToken);
                $entityManager->flush(); // Sauvegarder les changements
            }
        }

        // 3. Créer une réponse avec suppression des cookies
        $response = new JsonResponse([
            'code' => 200,
            'message' => 'Déconnexion réussie',
        ]);

        // Supprimer les cookies BEARER et refresh_token
        $response->headers->clearCookie('BEARER', '/', null, true, true);
        $response->headers->clearCookie('refresh_token', '/', null, true, true);

        return $response;
    }
}
