<?php

declare(strict_types=1);

namespace App\Service\CoursWishesFormService;

use App\DTO\CreateUserDTO;
use App\Entity\CoursWishesForm;
use App\Entity\User;
use App\Exception\UnvalidatedWishesFormException;
use App\Service\UserControllerService\CreateOrEditUserService;
use Doctrine\ORM\EntityManagerInterface;

readonly class RegisterUserFromWishesFormService
{
    public function __construct(
        private ResolveWishesFormByTokenService $resolveWishesFormByTokenService,
        private CreateOrEditUserService $createOrEditUserService,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * L'inscription n'est possible qu'avec un lien à token valide (envoyé par
     * email après validation du dossier).
     */
    public function register(CreateUserDTO $dto): User
    {
        if (null === $dto->token) {
            throw new UnvalidatedWishesFormException();
        }

        $form = $this->resolveWishesFormByTokenService->resolveByRegistrationToken($dto->token);
        if (!$form instanceof CoursWishesForm) {
            throw new UnvalidatedWishesFormException();
        }

        $dto->email = $form->getEmail();
        $dto->prenom = $form->getPrenom();
        $dto->nom = $form->getNom();
        $dto->telephone = $form->getTelephone();

        $user = $this->createOrEditUserService->createOrEditUser(null, $dto);

        $form->setUser($user);
        $form->setRegistrationTokenHash(null);
        $form->setRegistrationTokenExpiresAt(null);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
