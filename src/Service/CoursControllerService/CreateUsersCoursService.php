<?php

namespace App\Service\CoursControllerService;

use App\Entity\Cours;
use App\Entity\User;
use App\Manager\UsersCoursManager;
use App\Service\CoursControllerService\AddUserService\AddUserTimeCheckerService;
use App\Service\CoursControllerService\AddUserService\CheckIfCoursIsFullService;
use App\Service\CoursControllerService\AddUserService\CoursParticipationService;
use App\Service\CoursControllerService\AddUserService\HandleUserCreditService;
use App\Service\CoursControllerService\AddUserService\UserCreditCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

readonly class CreateUsersCoursService
{
    public function __construct(
        private CountUsersInCoursService $countUsersInCoursService,
        private UsersCoursManager $usersCoursManager,
        private AddUserTimeCheckerService $addUserTimeCheckerService,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private Security $security,
        private UserCreditCheckerService $userCreditCheckerService,
        private CheckIfCoursIsFullService $checkIfCoursIsFullService,
        private CoursParticipationService $coursParticipationService,
        private HandleUserCreditService $handleUserCreditService,
    ) {
    }

    public function createUsersCours(Cours $cours, User $user, bool $isOnWaitingList = false): JsonResponse
    {
        try {
            // Si l'heure du cours est passée - 30 minutes, on ne peut plus s'inscrire -> renvoie une exception
            $this->addUserTimeCheckerService->isTooLateRegister($cours);
            // Calcul du nombre de participants au cours
            $usersCount = $this->countUsersInCoursService->countUsers($cours);
            // Si l'admin ajoute un extra user, on ne vérifie pas si le cours est complet et on return
            if ($user !== $this->security->getUser()) {
                return $this->addExtraUser($cours, $user);
            }
            // Si l'utilisateur n'a pas assez de crédits, on ne peut pas s'inscrire -> renvoie une exception
            $this->userCreditCheckerService->checkAccountCredits($user, false);
            // Si le cours est désormais complet, on ne peut plus s'inscrire -> renvoie une exception
            $this->checkIfCoursIsFullService->checkIfCoursIsFull($cours, $usersCount, $isOnWaitingList);
            // Si le cours est complet, j'ajoute l'utilisateur à la liste d'attente sinon je l'ajoute au cours
            $this->coursParticipationService->addUserToCoursOrWaitingList($cours, $user, $isOnWaitingList);
            // Si désormais le cours est complet, je change le statut du cours
            $this->checkIfCoursIsFullService->changeStatusIfCoursIsfull($cours);
            // Si l'utilisateur n'est pas en attente alors on décrémente le nombre de cours de l'utilisateur
            $this->handleUserCreditService->decrement($isOnWaitingList);

            // Sauvegarde des modifications en base de données
            $this->em->persist($cours);
            $this->em->flush();

            // Retourne une réponse JSON pour indiquer que l'utilisateur a été ajouté avec succès
            return new JsonResponse([
                'success' => true,
                'message' => $isOnWaitingList ? "Vous êtes sur la liste d'attente" : 'Vous êtes bien inscrit au cours',
                'statusChange' => $this->serializer->serialize($cours->getStatusCours(), 'json', ['groups' => 'cours:detail']),
                'usersCount' => $this->countUsersInCoursService->countUsers($cours),
                'userCoursQuantity' => $user->getNombreCours()],
                Response::HTTP_OK);
        } catch (
            \Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()]);
            }
    }

    public function addExtraUser(Cours $cours, User $user): JsonResponse
    {
        // Si l'utilisateur n'a pas assez de crédits, on ne peut pas s'inscrire -> renvoie une exception
        $this->userCreditCheckerService->checkAccountCredits($user, true);

        $cours = $this->usersCoursManager->addUserToCours($cours, false, $user);
        $this->checkIfCoursIsFullService->changeStatusIfCoursIsfull($cours);
        $user->setNombreCours($user->getNombreCours() - 1);
        $this->em->persist($cours);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $user->getPrenom().' '.$user->getNom().' a bien été ajouté au cours',
            'statusChange' => $this->serializer->serialize($cours->getStatusCours(), 'json', ['groups' => 'cours:detail']),
            'usersCount' => $this->countUsersInCoursService->countUsers($cours),
        ],
            Response::HTTP_OK);
    }
}
