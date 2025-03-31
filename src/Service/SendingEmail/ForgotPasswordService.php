<?php

namespace App\Service\SendingEmail;

use App\Entity\User;
use App\Message\RemoveResetTokenMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

readonly class ForgotPasswordService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SendResetPasswordEmailService $sendResetPasswordEmailService,
        private MessageBusInterface $messageBus,
        private TokenGeneratorInterface $tokenGenerator,
    ) {
    }

    public function handleForgotPassword(string $emailReceived): JsonResponse
    {
        // Vérifie si l'email existe dans la base de données
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $emailReceived]);
        // Si l'utilisateur n'existe pas, on renvoie une erreur
        if (null === $user) {
            return new JsonResponse([
                'type' => 'error',
                'message' => "Il n'y a pas d'utilisateur avec cet email",
            ], Response::HTTP_NOT_FOUND);
        }

        // Si l'utilisateur existe, on lui envoie un email de réinitialisation de mot de passe
        try {
            // Recupere un token de réinitialisation
            $token = $this->tokenGenerator->generateToken();
            $user->setResetPasswordToken($token);
            $this->em->persist($user);
            $this->em->flush();

            // Envoie un message pour supprimer le token après 10 minutes
            $this->messageBus->dispatch(
                new RemoveResetTokenMessage(
                    $user->getId()),
                [new DelayStamp(10 * 60 * 1000)],
            );
            // Envoie un email de réinitialisation de mot de passe
            $this->sendResetPasswordEmailService->send($user, $token);

            return new JsonResponse([
                'type' => 'success',
                'message' => 'Un email de réinitialisation de mot de passe a été envoyé',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'type' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
