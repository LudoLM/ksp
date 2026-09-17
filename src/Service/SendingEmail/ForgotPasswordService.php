<?php

namespace App\Service\SendingEmail;

use App\Entity\User;
use App\Message\RemoveResetTokenMessage;
use App\Message\SendResetPasswordEmailMessage;
use App\Service\Security\SecureTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

readonly class ForgotPasswordService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $messageBus,
        private SecureTokenService $secureTokenService,
    ) {
    }

    public function handleForgotPassword(string $emailReceived): JsonResponse
    {
        // Vérifie si l'email existe dans la base de données
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $emailReceived]);

        // Si l'utilisateur n'existe pas, on retourne quand même un message de succès
        if (null === $user) {
            return new JsonResponse([
                'type' => 'success',
                'message' => 'Si cet email est enregistré, un lien de réinitialisation a été envoyé',
            ], Response::HTTP_OK);
        }

        // Si l'utilisateur existe, on lui envoie un email de réinitialisation de mot de passe
        try {
            // Recupere un token de réinitialisation
            $token = $this->secureTokenService->generate();

            // Hash le token avant stockage (KSP-11 security fix)
            $user->setResetPasswordToken($this->secureTokenService->hash($token));

            // Définit la date d'expiration à 10 minutes (KSP-11 security fix)
            $expiresAt = new \DateTime('+10 minutes');
            $user->setResetPasswordTokenExpiresAt($expiresAt);

            $this->em->persist($user);
            $this->em->flush();

            // Envoie un message pour supprimer le token après 10 minutes et mail de réinitialisation
            $this->messageBus->dispatch(
                new SendResetPasswordEmailMessage($user->getId(), $token)
            );
            $this->messageBus->dispatch(
                new RemoveResetTokenMessage($user->getId()),
                [new DelayStamp(10 * 60 * 1000)]
            );

            return new JsonResponse([
                'type' => 'success',
                'message' => 'Si cet email est enregistré, un lien de réinitialisation a été envoyé',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'type' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
