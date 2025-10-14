<?php

namespace App\Service\UserControllerService;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class FetchUserService
{
    public function __construct(
        private UserRepository $userRepository,
        private Security $security,
    ) {
    }

    public function fetchUser(?int $id = null): User
    {
        // Si un ID est fourni, on cherche cet utilisateur
        if (null !== $id) {
            $user = $this->userRepository->find($id);

            if (!$user instanceof User) {
                throw new NotFoundHttpException('Utilisateur non trouvé');
            }

            // Vérifier les droits d'accès
            if (!$this->security->isGranted(UserVoter::VIEW, $user)) {
                throw new AccessDeniedException('Accès refusé à ce profil');
            }

            return $user;
        }

        // Sinon, on récupère l'utilisateur connecté
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException('Authentification requise');
        }

        return $user;
    }
}
