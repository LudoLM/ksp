<?php


namespace App\Controller\Api;


use App\Entity\HistoriquePaiement;
use App\Entity\Pack;
use App\Repository\HistoriquePaiementRepository;
use App\Repository\PackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;


#[Route(path: 'api/', name: 'api_')]
class PackController extends AbstractController
{

//    #[Route('packs', name: 'packs_index', methods: ['GET'])]
//    public function index(PackRepository $packRepository)
//    {
//        $packs = $packRepository->findAll();
//        return $this->json($packs);
//    }
//
//    public function show(Pack $pack)
//    {
//        return $this->json($pack);
//    }

    #[Route('merci/{id}', name: 'merci', methods: ['POST'])]
    public function merci(
        string $id,
        EntityManagerInterface $entityManager,
        PackRepository $packRepository,
        HistoriquePaiementRepository $historiquePaiementRepository
    ) : JsonResponse
    {
        // Si l'id n'existe pas dans la table historiquePaiment->checkoutId, on continue
        if($historiquePaiementRepository->findBy(['checkoutId' => $id])){
            return $this->json(['message' => 'Paiement déjà effectué'], 400);
        }
        else{
            $stripe = new StripeClient($_ENV['STRIPE_PRIVATE_KEY']);
            $session = $stripe->checkout->sessions->retrieve($id, []); // on retrive la session pour vérifier si le paiement a été effectué
            $nbCours = $stripe->checkout->sessions->allLineItems($id, ['expand' => ['data.price.product']]); // on retrive les LI pour avoir le nombre de cours
            $nbCours = json_decode($nbCours->toJSON(), true);
            $credits = $nbCours['data'][0]['price']['product']['metadata']['nombreCours'];

            if ($session->payment_status === 'paid') {
                $packName = $nbCours['data'][0]['price']['product']['name'];
                $packAmount = $nbCours['data'][0]['price']['unit_amount'];

                $historiquePaiement = new HistoriquePaiement();
                $historiquePaiement->setCheckoutId($id);
                $historiquePaiement->setUser($this->getUser());

                //Si le pack n'existe pas, on le crée
                if(empty($packRepository->findBy(['nom' => $packName]))){
                    $pack = new Pack();
                    $pack->setNom($packName);
                    $pack->setTarif($packAmount);
                    $pack->setNombreCours($credits);
                }
                else{
                    $pack = $packRepository->findBy(['nom' => $packName])[0];
                }
                $historiquePaiement->setPack($pack);
                $historiquePaiement->setDate(new \DateTime());
                $entityManager->persist($historiquePaiement);
                $entityManager->flush();
                $user = $this->getUser();

                //On ajoute les crédits au nombre de cours de l'utilisateur
                $user->setNombreCours($user->getNombreCours() + $credits);
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->json(['message' => 'Paiement effectué'], status: 200);
            } else {
                return $this->json(['message' => 'Paiement non effectué'], 400);
            }
        }

    }

}