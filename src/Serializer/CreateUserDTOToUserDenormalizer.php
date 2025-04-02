<?php

namespace App\Serializer;

use App\DTO\CreateUserDTO;
use App\DTO\EditUserDTO;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class CreateUserDTOToUserDenormalizer implements DenormalizerInterface
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function denormalize($data, string $type, ?string $format = null, array $context = [])
    {
        if (!$data instanceof CreateUserDTO && !$data instanceof EditUserDTO) {
            throw new \Exception('Instance de CreateUserDTO ou EditUSerDTO attendue');
        }
        if (array_key_exists('object_to_populate', $context) && $context['object_to_populate'] instanceof User) {
            $user = $context['object_to_populate'];
        } else {
            $user = new User();
            $user->setRoles(['ROLE_USER']);
            $user->setNombreCours(0);
            $user->setEmail($data->email);
            //         Hashage du mot de passe
            $hashedPassword = $this->userPasswordHasher->hashPassword(
                $user,
                $data->password
            );
            $user->setPassword($hashedPassword);
        }

        $user->setPrenom($data->prenom);
        $user->setNom($data->nom);
        $user->setTelephone($data->telephone);
        $user->setAdresse($data->adresse);
        $user->setCodePostal($data->cp);
        $user->setCommune($data->commune);

        return $user;
    }

    public function supportsDenormalization($data, string $type, ?string $format = null): bool
    {
        return User::class === $type;
    }
}
