<?php

namespace App\Service\UserControllerService;

use App\Entity\User;
use App\Serializer\CreateUserDTOToUserDenormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class CreateOrEditUserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CreateUserDTOToUserDenormalizer $createUserDTOToUserDenormalizer,
        private ValidatorInterface $validator,
    ) {
    }

    public function createOrEditUser($editableUser, $userDTO)
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

        return $user;
    }
}
