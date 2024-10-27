<?php

namespace App\Controller\Api;


use App\DTO\CreateCoursDTO;
use App\Entity\Cours;
use App\Entity\UsersCours;
use App\Enum\StatusCoursEnum;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Service\UpdateStatusCours;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

#[Route(path: "api/", name:"api")]
class CoursController extends AbstractController
{

    public function __construct(
        private readonly CoursRepository $coursRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly TypeCoursRepository $typeCoursRepository,
        private readonly UpdateStatusCours $updateStatusCours,
        private readonly MailerInterface $mailer
    )
    {

    }

    #[Route('getCours', name: 'cours_index', methods: ['GET'])]
    public function coursIndex(Request $request): JsonResponse
    {
        $role = $request->headers->get("X-ACCESS-TOKEN");
        if ($role === 'ROLE_ADMIN') {
            $cours = $this->coursRepository->findAllSortByDate();
        } else {
            $cours = $this->coursRepository->findAllSortByDateForUsers();
        }
        $cours = $this->updateStatusCours->updateStatusCours($cours);

        $jsonCours = $this->serializer->serialize($cours, 'json', ['groups' => 'cours:index']);
        return new JsonResponse($jsonCours, 200, [], true);
    }


    #[Route('getCours/{id}', name: 'cours_detail', methods: ['GET'])]
    public function coursFiltered(int $id): JsonResponse
    {
        $cours = $this->coursRepository->find($id);
        $jsonCours = $this->serializer->serialize($cours, 'json', ['groups' => 'cours:detail']);

        return new JsonResponse($jsonCours);
    }

    #[Route('addUser/{id}/{isAttente?}', name: 'cours_add_user', methods: ['POST'])]
    public function addUserToCours(Cours $cours, ?string $isAttente): JsonResponse
    {

        $user = $this->getUser();
        $isAttente =  $isAttente === 'true' ? true : false;
        $statusChange = $cours->getStatusCours()->getLibelle();

        if (in_array($user->getId(), array_map(fn($usersCours) => $usersCours->getUser()->getId(), $user->getUsersCours()->toArray()))) {
            foreach ($user->getUsersCours() as $usersCours) {
                if ($usersCours->getUser()->getId() === $user->getId()) {
                    $usersCours->setEnAttente(false);
                }
            }
        }
        else{
            $usersCours = new UsersCours();
            $usersCours->setUser($user);
            $usersCours->setEnAttente($isAttente);
        }


        $usersCours->setCreatedAt(new \DateTimeImmutable());
        $cours->addUsersCours($usersCours);

        if (count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();})) >= $cours->getNbInscriptionMax()) {
            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::COMPLET->value]));
            $statusChange = StatusCoursEnum::COMPLET->value;
        }


        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();}));
        $this->getUser()->setNombreCours($this->getUser()->getNombreCours() - 1);


        // Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été ajouté avec succès
        return new JsonResponse(
            ['response' => true, 'statusChange' => $statusChange, "usersCount" => $usersCount], 200);
    }


    #[Route('removeUser/{id}', name: 'cours_remove_user')]
    public function removeUserFromCours(Cours $cours): JsonResponse
    {
        $user = $this->getUser();
        $statusChange = $cours->getStatusCours()->getLibelle();
        // Suppression de l'utilisateur du cours
        foreach ($cours->getUsersCours() as $usersCours) {
            if ($usersCours->getUser() === $user) {
                $cours->removeUsersCours($usersCours);
            }
        }
        $this->getUser()->setNombreCours($this->getUser()->getNombreCours() + 1);

//        Envoyer mail aux utilisateurs de la liste attente du cours
//        if ($cours->getStatusCours()->getLibelle() === StatusCoursEnum::COMPLET->value) {
//            foreach ($cours->getUsersCours() as $participant) {
//                if ($participant->isEnAttente()) {
//                    $email = (new TemplatedEmail())
//                        ->from('test@test.fr')
//                        ->to('test@test.fr')
//                        ->subject('Place disponible pour le cours de ' . $cours->getTypeCours()->getLibelle())
//                        ->htmlTemplate('emails/attente.html.twig')
//                        ->locale('fr')
//                        ->context([
//                            'cours' => $cours,
//                            'participant' => $participant->getUser(),
//                            'user' => $this->getUser()
//                        ]);
//                    $this->mailer->send($email);
//                }
//            }
//        }


        if(count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();})) < $cours->getNbInscriptionMax() && $cours->getStatusCours()->getLibelle() === StatusCoursEnum::COMPLET->value) {

            $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));
            $statusChange = StatusCoursEnum::OUVERT->value;
        }

        $usersCount = count(array_filter($cours->getUsersCours()->toArray(), function ($usersCours) {return !$usersCours->isEnAttente();}));

        // Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        // Retourne une réponse JSON pour indiquer que l'utilisateur a été supprimé avec succès
        return new JsonResponse(['response' => true, 'statusChange' => $statusChange, 'usersCount' => $usersCount], 200);
    }

    // Add route for create new cours
    #[Route('cours/create', name: 'cours_create', methods: ['POST'])]
    #IsGranted("ROLE_ADMIN")
    public function createCours(
        Request $request,
        #[MapRequestPayload(
            serializationContext: [
                'groups' => ['cours:create']
            ]
        )]
        CreateCoursDTO $coursDTO
    ) : JsonResponse
    {
        $newCours = new Cours();
        $newCours->setDuree($coursDTO->dureeCours);
        $newCours->setDateCours($coursDTO->dateCours);
        $newCours->setDescription($coursDTO->description);
        $newCours->setTarif($coursDTO->tarif);
        $newCours->setNbInscriptionMax($coursDTO->nbInscriptionMax);
        $newCours->setTypeCours($this->typeCoursRepository->find($coursDTO->typeCours));
        $newCours->setDateLimiteInscription($coursDTO->dateLimiteInscription);
        $newCours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::EN_CREATION->value]));
        $this->em->persist($newCours);
        $this->em->flush();

        return new JsonResponse(['response' => true], 200);
    }


    //Delete route for delete cours
    #[Route('cours/delete/{id}', name: 'cours_delete', methods: ['DELETE'])]
    public function deleteCours(Cours $cours): JsonResponse
    {
        $this->em->remove($cours);
        $this->em->flush();

        return new JsonResponse(['response' => true], 200);
    }

    #[Route('cours/open/{id}', name: 'cours_open', methods: ['PUT'])]
    public function openCours(Cours $cours): JsonResponse
    {
        $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::OUVERT->value]));

        $this->em->persist($cours);
        $this->em->flush();

        return new JsonResponse(['response' => true, 'statusChange' => StatusCoursEnum::OUVERT->value], 200);
    }

    #[Route('cours/cancel/{id}', name: 'cours_cancel', methods: ['PUT'])]
    public function cancelCours(Cours $cours): JsonResponse
    {
        $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::ANNULE->value]));
        $this->em->persist($cours);

        foreach ($cours->getUsersCours() as $participant){
            $email = (new TemplatedEmail())
                ->from('test@test.fr')
                ->to('test@test.fr')
                ->subject('Annulation du cours')
                ->htmlTemplate('emails/cancel.html.twig')
                ->locale('fr')
                ->context([
                    'cours' => $cours,
                    'participant' => $participant->getUser(),
                    'user' => $this->getUser()
                ]);
            $this->mailer->send($email);
            $participant->getUser()->setNombreCours($participant->getUser()->getNombreCours() + 1);
            $this->em->persist($participant->getUser());
        }

        $this->em->flush();

        return new JsonResponse(['response' => true, 'statusChange' => StatusCoursEnum::ANNULE->value], 200);
    }


    #[Route('cours/edit/{id}', name: 'cours_update', methods: ['PUT'])]
    public function editCours(
        Cours $cours,
        #[MapRequestPayload(
            serializationContext: [
                'groups' => ['cours:create']
            ]
        )]
        CreateCoursDTO $coursDTO
    ) : JsonResponse
    {
        $cours->setDuree($coursDTO->dureeCours);
        $cours->setDateCours($coursDTO->dateCours);
        $cours->setDescription($coursDTO->description);
        $cours->setTarif($coursDTO->tarif);
        $cours->setNbInscriptionMax($coursDTO->nbInscriptionMax);
        $cours->setTypeCours($this->typeCoursRepository->find($coursDTO->typeCours));
        $cours->setDateLimiteInscription($coursDTO->dateLimiteInscription);
        $cours->setStatusCours($cours->getStatusCours());


        $this->em->persist($cours);
        $this->em->flush();

        return new JsonResponse(['response' => true], 200);
    }

}