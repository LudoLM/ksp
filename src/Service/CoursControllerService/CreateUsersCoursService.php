<?php

namespace App\Service\CoursControllerService;

use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Entity\User;
use App\Enum\StatusCoursEnum;
use App\Manager\UsersCoursManager;
use App\Repository\StatusCoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class CreateUsersCoursService
{
    public function __construct(
        private readonly CountUsersInCoursService $countUsersInCoursService,
        private readonly UsersCoursManager $usersCoursManager,
        private readonly AddUserTimeCheckerService $addUserTimeCheckerService,
        private readonly EntityManagerInterface $em,
        private readonly StatusCoursRepository $statusCoursRepository,
        private readonly SerializerInterface $serializer,
        private readonly Security $security,
    ) {
    }

    public function createUsersCours(Cours $cours, $user, $isOnWaitingList = false): JsonResponse
    {
        if (!$user instanceof User) {
            throw new \Exception('Type de l\'utilisateur invalide');
        }
        //      Si l'heure du cours est passée - 30 minutes, on ne peut plus s'inscrire
        if ($this->addUserTimeCheckerService->isTooLateRegister($cours)) {
            return new JsonResponse([
                'success' => false,
                'message' => "Il est trop tard pour s'inscrire à ce cours"],
                Response::HTTP_FORBIDDEN);
        }
        //      Calcul du nombre de participants au cours
        $usersCount = $this->countUsersInCoursService->countUsers($cours);
        //      Récupération du statut du cours
        $statusChange = $cours->getStatusCours();

        //      Si l'admin ajoute un extra user, on ne vérifie pas si le cours est complet
        if ($user !== $this->security->getUser()) {
            // Si l'utilisateur n'a pas assez de crédits, on ne peut pas s'inscrire
            if ($user->getNombreCours() <= 0) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $user->getPrenom().' '.$user->getNom()." n'a pas assez de crédits pour s'inscrire à ce cours"],
                    Response::HTTP_FORBIDDEN);
            }

            $this->addExtraUser($cours, $user, $statusChange);

            return new JsonResponse([
                'success' => true,
                'response' => $user->getPrenom().' '.$user->getNom().' a bien été ajouté au cours',
                'statusChange' => $this->serializer->serialize($cours->getStatusCours(), 'json', ['groups' => 'cours:detail']),
                'usersCount' => $usersCount,
            ],
                Response::HTTP_OK);
        }

        // Si l'utilisateur n'a pas assez de crédits, on ne peut pas s'inscrire
        if ($user->getNombreCours() <= 0) {
            return new JsonResponse(['success' => false, 'message' => "Vous n'avez pas assez de crédits pour vous inscrire à ce cours"], Response::HTTP_FORBIDDEN);
        }

        //      Si le cours est désormais complet, on ne peut plus s'inscrire
        if ($usersCount >= $cours->getNbInscriptionMax() && !$isOnWaitingList) {
            return new JsonResponse(['success' => false, 'message' => 'Le cours est complet', 'statusChange' => $statusChange, 'usersCount' => $usersCount], Response::HTTP_OK);
        }

        //      Si le cours n'est pas complet, je vérifie si l'utilisateur est déjà inscrit ou en attente
        $usersCoursFiltered = array_filter($cours->getUsersCours()->toArray(), fn ($usersCours): bool => $usersCours->getUser() === $user);

        //      Si l'utilisateur est déjà en attente, je le passe en inscrit
        if (count($usersCoursFiltered) > 0) {
            $usersCours = array_values($usersCoursFiltered)[0];
            $usersCours->setIsOnWaitingList(false);
            $usersCours->setCreatedAt(new \DateTimeImmutable());
        } //      Si l'utilisateur n'est pas inscrit, je l'ajoute à la liste des participants
        else {
            $cours = $this->usersCoursManager->addUserToCours($cours, $isOnWaitingList, $user);
        }

        //      Si le cours est complet, je change le statut du cours
        $usersCount = $this->countUsersInCoursService->countUsers($cours);
        $isFull = $usersCount >= $cours->getNbInscriptionMax();
        if ($isFull) {
            $statusChange = $this->changeStatus($cours);
        }

        //       Si le cours n'est pas en attente alors on décrémente le nombre de cours de l'utilisateur
        if (!$isOnWaitingList) {
            // Vérifiez que $user est une instance de la classe utilisateur
            $this->security->getUser()->setNombreCours($this->security->getUser()->getNombreCours() - 1);
        }

        //      Sauvegarde des modifications en base de données
        $this->em->persist($cours);
        $this->em->flush();

        //      Retourne une réponse JSON pour indiquer que l'utilisateur a été ajouté avec succès
        return new JsonResponse([
            'success' => true,
            'message' => $isOnWaitingList ? "Vous êtes sur la liste d'attente" : 'Vous êtes bien inscrit au cours',
            'statusChange' => $this->serializer->serialize($statusChange, 'json', ['groups' => 'cours:detail']),
            'usersCount' => $usersCount,
            'userCoursQuantity' => $user->getNombreCours()],
            Response::HTTP_OK);
    }

    public function changeStatus(Cours $cours): StatusCours
    {
        $cours->setStatusCours($this->statusCoursRepository->findOneBy(['libelle' => StatusCoursEnum::COMPLET->value]));

        return $cours->getStatusCours();
    }

    public function addExtraUser(Cours $cours, User $user, StatusCours $statusChange): StatusCours
    {
        $cours = $this->usersCoursManager->addUserToCours($cours, false, $user);
        $user->setNombreCours($user->getNombreCours() - 1);
        $usersCount = $this->countUsersInCoursService->countUsers($cours);
        $isFull = $usersCount >= $cours->getNbInscriptionMax();
        //      Si le cours est complet, je change le statut du cours
        if ($isFull && $cours->getStatusCours()->getLibelle() !== StatusCoursEnum::COMPLET->value) {
            $statusChange = $this->changeStatus($cours);
        }
        $this->em->persist($cours);
        $this->em->flush();

        return $statusChange;
    }
}
