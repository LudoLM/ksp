<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\User;
use App\Repository\CoursWishesFormRepository;
use App\Service\Security\SecureTokenService;

readonly class ResolveWishesFormByTokenService
{
    public function __construct(
        private CoursWishesFormRepository $repository,
        private SecureTokenService $secureTokenService,
    ) {
    }

    public function resolveByCorrectionToken(string $token): ?CoursWishesForm
    {
        return $this->resolve(
            $token,
            'correctionTokenHash',
            static fn (CoursWishesForm $form): ?\DateTimeImmutable => $form->getCorrectionTokenExpiresAt(),
        );
    }

    public function resolveByRegistrationToken(string $token): ?CoursWishesForm
    {
        return $this->resolve(
            $token,
            'registrationTokenHash',
            static fn (CoursWishesForm $form): ?\DateTimeImmutable => $form->getRegistrationTokenExpiresAt(),
            // Un dossier déjà lié à un compte a déjà consommé son token (celui-ci
            // est normalement remis à null à ce moment-là) : re-vérifier ici
            // réduit la fenêtre de course si deux requêtes concurrentes arrivent
            // avec le même token avant que la première n'ait flush son update.
            rejectIfLinkedToUser: true,
        );
    }

    private function resolve(string $token, string $hashField, callable $expiresAtGetter, bool $rejectIfLinkedToUser = false): ?CoursWishesForm
    {
        $form = $this->repository->findOneBy([
            $hashField => $this->secureTokenService->hash($token),
        ]);

        if (!$form instanceof CoursWishesForm) {
            return null;
        }

        if ($rejectIfLinkedToUser && $form->getUser() instanceof User) {
            return null;
        }

        $expiresAt = $expiresAtGetter($form);
        if (!$expiresAt instanceof \DateTimeImmutable || $expiresAt < new \DateTimeImmutable()) {
            return null;
        }

        return $form;
    }
}
