<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\StripeService\StripeCheckoutCreditService;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'StripeWebhook')]
class StripeWebhookController extends AbstractController
{
    public function __construct(
        private readonly string $webhookSecret,
        private readonly UserRepository $userRepository,
        private readonly StripeCheckoutCreditService $stripeCheckoutCreditService,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('api/public/webhook/stripe', name: 'stripe_webhook', methods: ['POST'])]
    public function handle(Request $request): JsonResponse
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->headers->get('Stripe-Signature'),
                $this->webhookSecret,
            );
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            $this->logger->warning('Signature Stripe webhook invalide', [
                'error' => $e->getMessage(),
            ]);

            return $this->json(['error' => 'Signature invalide'], 400);
        }

        if ('checkout.session.completed' !== $event->type) {
            return $this->json(['message' => 'Événement ignoré']);
        }

        return $this->handleCheckoutSessionCompleted($event);
    }

    private function handleCheckoutSessionCompleted(Event $event): JsonResponse
    {
        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object; // @phpstan-ignore property.notFound (StripeObject imbriqué dynamique, cf. doc Stripe Event::$data)
        $checkoutSessionId = $session->id;
        $userId = $session->client_reference_id;

        if (null === $userId || '' === $userId) {
            $this->logger->warning('Webhook Stripe checkout.session.completed sans client_reference_id', [
                'checkout_id' => $checkoutSessionId,
            ]);

            return $this->json(['message' => 'Aucun utilisateur associé à ce paiement']);
        }

        $user = $this->userRepository->find($userId);
        if (!$user instanceof User) {
            $this->logger->warning('Webhook Stripe checkout.session.completed : utilisateur introuvable', [
                'checkout_id' => $checkoutSessionId,
                'user_id' => $userId,
            ]);

            return $this->json(['message' => 'Utilisateur introuvable']);
        }

        try {
            $result = $this->stripeCheckoutCreditService->creditUserForCheckoutSession($user, $checkoutSessionId);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur lors du traitement du webhook Stripe', [
                'error' => $e->getMessage(),
                'checkout_id' => $checkoutSessionId,
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->json(['error' => 'Erreur lors du traitement du paiement'], 500);
        }

        return $this->json(['message' => $result['message']]);
    }
}
