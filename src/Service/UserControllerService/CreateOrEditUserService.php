<?php

namespace App\Service\UserControllerService;

use App\Entity\User;
use App\Serializer\CreateUserDTOToUserDenormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class CreateOrEditUserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CreateUserDTOToUserDenormalizer $createUserDTOToUserDenormalizer,
        private ValidatorInterface $validator,
        private JWTTokenManagerInterface $JWTManager,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function createOrEditUser($editableUser, $userDTO): string
    {
        if (null !== $editableUser) {
            $user = $this->createUserDTOToUserDenormalizer->denormalize($userDTO, User::class, context: ['object_to_populate' => $editableUser]);
        } else {
            $user = $this->createUserDTOToUserDenormalizer->denormalize($userDTO, User::class);
        }
        // Valider l'entité user
        $violations = $this->validator->validate($user);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    $violation->getPropertyPath() => $violation->getMessage(),
                ];
            }

            throw new \InvalidArgumentException(json_encode(['errors' => $errors]));
        }

        $this->em->persist($user);
        $this->em->flush();

        $token = $this->JWTManager->create($user);
        $authenticatedToken = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($authenticatedToken);

        return $token;
    }
}
