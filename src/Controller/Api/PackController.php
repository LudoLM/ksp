<?php

namespace App\Controller\Api;

use App\Entity\HistoriquePaiement;
use App\Entity\Pack;
use App\Entity\User;
use App\Repository\HistoriquePaiementRepository;
use App\Repository\PackRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Pack')]
class PackController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('api/packs', name: 'packs_list', methods: ['GET'])]
    public function index(PackRepository $packRepository): JsonResponse
    {
        $packs = $packRepository->findAll();

        return $this->json($packs);
    }

    public function show(Pack $pack): JsonResponse
    {
        return $this->json($pack);
    }

    #[Route('api/merci/{id}', name: 'merci', methods: ['POST'])]
    public function merci(
        string $id,
        EntityManagerInterface $entityManager,
        PackRepository $packRepository,
        HistoriquePaiementRepository $historiquePaiementRepository,
    ): JsonResponse {
        // Si l'id n'existe pas dans la table historiquePaiment->checkoutId, on continue
        if ([] !== $historiquePaiementRepository->findBy(['checkoutId' => $id])) {
            return $this->json(['message' => 'Paiement déjà effectué'], 400);
        }
        $stripe = new StripeClient($_ENV['STRIPE_PRIVATE_KEY']);
        $session = $stripe->checkout->sessions->retrieve($id, []);
        // on retrive la session pour vérifier si le paiement a été effectué
        $nbCours = $stripe->checkout->sessions->allLineItems($id, ['expand' => ['data.price.product']]);
        // on retrive les LI pour avoir le nombre de cours

        // Sécurisation: Décoder avec gestion d'erreur (KSP-12)
        try {
            $nbCours = json_decode(
                (string) $nbCours->toJSON(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            // Log l'erreur pour debug paiements (CRITICAL - argent)
            $this->logger->error('Stripe JSON decode error', [
                'error' => $e->getMessage(),
                'checkout_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->json([
                'type' => 'error',
                'message' => 'Erreur lors du traitement du paiement',
            ], 500);
        }

        // Validation: Vérifier la structure des données Stripe (KSP-12)
        if (!isset($nbCours['data'][0]['price']['product']['metadata']['nombreCours'])) {
            $this->logger->error('Stripe response structure invalid', [
                'checkout_id' => $id,
                'data' => $nbCours,
            ]);

            return $this->json([
                'type' => 'error',
                'message' => 'Données de paiement incomplètes',
            ], 500);
        }

        $credits = $nbCours['data'][0]['price']['product']['metadata']['nombreCours'];
        if ('paid' === $session->payment_status) {
            $packName = $nbCours['data'][0]['price']['product']['name'];
            $packAmount = $nbCours['data'][0]['price']['unit_amount'];

            $historiquePaiement = new HistoriquePaiement();
            $historiquePaiement->setCheckoutId($id);
            $user = $this->getUser();
            if ($user instanceof User) {
                $historiquePaiement->setUser($user);
                if ($credits > 10) {
                    $user->setIsPrioritized(true);
                }
            } else {
                throw new \Exception('L\'utilisateur n\'est pas valide');
            }

            // Si le pack n'existe pas, on le crée
            if (0 === count($packRepository->findBy(['nom' => $packName]))) {
                $pack = new Pack();
                $pack->setNom($packName);
                $pack->setTarif($packAmount);
                $pack->setNombreCours($credits);
            } else {
                $pack = $packRepository->findBy(['nom' => $packName])[0];
            }
            $historiquePaiement->setPack($pack);
            $historiquePaiement->setDate(new \DateTime());
            $entityManager->persist($historiquePaiement);
            $entityManager->flush();
            $user = $this->getUser();

            // Vérifiez que $user est une instance de la classe utilisateur
            if (!$user instanceof User) {
                throw new \Exception('Type de l\'utilisateur invalide');
            }

            // On ajoute les crédits au nombre de cours de l'utilisateur
            $user->setNombreCours($user->getNombreCours() + $credits);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json([
                'message' => 'Paiement effectué',
                'userQuantity' => $user->getNombreCours(),
            ], status: 200);
        }

        return $this->json(['message' => 'Paiement non effectué'], 400);
    }
}
