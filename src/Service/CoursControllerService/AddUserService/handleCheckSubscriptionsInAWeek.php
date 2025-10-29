<?php

namespace App\Service\CoursControllerService\AddUserService;

use App\Entity\Cours;
use App\Entity\User;
use App\Entity\UsersCours;

class handleCheckSubscriptionsInAWeek
{
    public function checkIfUserAlreadyHasTwoSubscriptionsInTheSameWeek(User $user, Cours $cours): void
    {
        // Logique pour vérifier si l'utilisateur a déjà deux abonnements dans la même semaine
        // Si oui, lancer une exception ou gérer le cas selon les besoins
        // Exemple :
        $subscriptionsCount = $this->countUserSubscriptionsInWeek($user, $cours);
        if ($subscriptionsCount >= 2) {
            throw new \InvalidArgumentException('Vous avez déjà deux reservations de cours pour cette semaine.', 403);
        }
    }

    private function countUserSubscriptionsInWeek(User $user, Cours $cours): int
    {
        // Implémenter la logique pour compter les abonnements de l'utilisateur dans la même semaine que le cours
        $dateCours = $cours->getDateCours();
        if ($dateCours instanceof \DateTime) {
            $dateCours = clone $dateCours;
        } else {
            throw new \InvalidArgumentException('La date du cours doit être une instance de DateTime.');
        }
        $mondayOfWeek = (clone $dateCours)->modify('monday this week');
        $usersCours = $user->getUsersCours();

        return count(array_filter($usersCours->toArray(), function (UsersCours $userCours) use ($mondayOfWeek): bool {
            $coursDate = $userCours->getCours()->getDateCours();

            return $coursDate >= $mondayOfWeek && $coursDate < (clone $mondayOfWeek)->modify('+1 week') && true !== $userCours->isOnWaitingList() && $userCours->getCours()->hasLimitOfOneCoursPerWeek();
        }));
    }
}
