<?php

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class RemoveResetTokenService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function removeResetToken(int $userId): void
    {
        $user = $this->userRepository->find($userId);
        if (null !== $user) {
            $user->setResetPasswordToken('');
            $this->em->persist($user);
            $this->em->flush();
        }
    }
}
