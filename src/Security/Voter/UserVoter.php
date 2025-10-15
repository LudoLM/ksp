<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const VIEW = 'USER_VIEW';

    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VIEW === $attribute && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $targetUser */
        $targetUser = $subject;
        $caller = $token->getUser();

        // Si l'appelant n'est pas un utilisateur connecté, accès refusé
        if (!$caller instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($caller, $targetUser),
            default => false,
        };
    }

    private function canView(User $caller, User $targetUser): bool
    {
        // Règle 1 : L'administrateur a toujours accès
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Règle 2 : L'utilisateur peut voir son propre profil
        return $caller->getId() === $targetUser->getId();
    }
}
