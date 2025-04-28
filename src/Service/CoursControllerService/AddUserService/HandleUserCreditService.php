<?php

namespace App\Service\CoursControllerService\AddUserService;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

readonly class HandleUserCreditService
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function decrement(bool $isOnWaitingList): void
    {
        if (!$isOnWaitingList) {
            $user = $this->security->getUser();

            if (!$user instanceof User) {
                throw new \LogicException('L\'utilisateur doit être une instance de .'.User::class.'.');
            }

            $user->setNombreCours($user->getNombreCours() - 1);
        }
    }
}
