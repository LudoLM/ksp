<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Repository\CoursWishesFormRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CoursWishesFormVoter extends Voter
{
    public const string VALIDE = 'COURS_WISHES_FORM_VALIDE';

    public function __construct(
        private readonly Security $security,
        private readonly CoursWishesFormRepository $repository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::VALIDE === $attribute;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $this->repository->hasValidatedFormForCurrentSaison($user);
    }
}
