<?php

namespace App\Service\StripeService;

use App\Entity\HistoriquePaiement;
use App\Entity\Pack;
use App\Entity\User;
use App\Repository\HistoriquePaiementRepository;
use App\Repository\PackRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\StripeClient;

class StripeCheckoutCreditService
{
    public function __construct(
        private readonly StripeClient $stripeClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly PackRepository $packRepository,
        private readonly HistoriquePaiementRepository $historiquePaiementRepository,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function creditUserForCheckoutSession(User $user, string $checkoutSessionId): array
    {
        if ([] !== $this->historiquePaiementRepository->findBy(['checkoutId' => $checkoutSessionId])) {
            return ['status' => 'already_processed', 'message' => 'Paiement déjà effectué'];
        }

        $session = $this->stripeClient->checkout->sessions->retrieve($checkoutSessionId, []);

        // Empêche un utilisateur authentifié de se créditer avec la session Stripe (payée) d'un
        // autre utilisateur en devinant/récupérant son checkoutSessionId (IDOR)
        if (null === $session->client_reference_id || (string) $user->getId() !== $session->client_reference_id) {
            $this->logger->warning('Tentative de crédit sur une session Stripe ne correspondant pas à l\'utilisateur authentifié', [
                'checkout_id' => $checkoutSessionId,
                'user_id' => $user->getId(),
            ]);

            return ['status' => 'forbidden', 'message' => 'Accès refusé'];
        }

        $lineItems = $this->stripeClient->checkout->sessions->allLineItems($checkoutSessionId, ['expand' => ['data.price.product']]);

        try {
            $lineItemsData = json_decode(
                (string) $lineItems->toJSON(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            $this->logger->error('Stripe JSON decode error', [
                'error' => $e->getMessage(),
                'checkout_id' => $checkoutSessionId,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        if (!isset($lineItemsData['data'][0]['price']['product']['metadata']['nombreCours'])) {
            $this->logger->error('Stripe response structure invalid', [
                'checkout_id' => $checkoutSessionId,
                'data' => $lineItemsData,
            ]);

            throw new \RuntimeException('Données de paiement incomplètes');
        }

        if ('paid' !== $session->payment_status) {
            return ['status' => 'not_paid', 'message' => 'Paiement non effectué'];
        }

        $credits = $lineItemsData['data'][0]['price']['product']['metadata']['nombreCours'];
        $packName = $lineItemsData['data'][0]['price']['product']['name'];
        $packAmount = $lineItemsData['data'][0]['price']['unit_amount'];

        $historiquePaiement = new HistoriquePaiement();
        $historiquePaiement->setCheckoutId($checkoutSessionId);
        $historiquePaiement->setUser($user);
        if ($credits > 10) {
            $user->setIsPrioritized(true);
        }

        $existingPacks = $this->packRepository->findBy(['nom' => $packName]);
        if ([] === $existingPacks) {
            $pack = new Pack();
            $pack->setNom($packName);
            $pack->setTarif($packAmount);
            $pack->setNombreCours($credits);
        } else {
            $pack = $existingPacks[0];
        }
        $historiquePaiement->setPack($pack);
        $historiquePaiement->setDate(new \DateTime());

        $this->entityManager->persist($historiquePaiement);
        $this->entityManager->persist($user);

        try {
            $this->entityManager->wrapInTransaction(function () use ($user, $credits): void {
                $this->entityManager->flush();
                $this->userRepository->addCredits($user->getId(), $credits);
            });
        } catch (UniqueConstraintViolationException) {
            return ['status' => 'already_processed', 'message' => 'Paiement déjà effectué'];
        }

        $this->entityManager->refresh($user);

        return [
            'status' => 'credited',
            'message' => 'Paiement effectué',
            'userQuantity' => $user->getNombreCours(),
        ];
    }
}
