<?php


namespace App\Serializer;

use AllowDynamicProperties;
use App\DTO\CreateUserDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AllowDynamicProperties]
class CreateUserDTOToUserDenormalizer implements DenormalizerInterface
{

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;

    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        /** @var \App\DTO\CreateUserDTO $data */
        if (!$data instanceof CreateUserDTO) {
            throw new \Exception('Expected instance of CreateUserDTO');
        }
        if (array_key_exists('object_to_populate', $context) &&  $context['object_to_populate'] instanceof User) {
            $user = $context['object_to_populate'];
        } else {
            $user = new User();
            $user->setRoles(['ROLE_USER']);
            $user->setNombreCours(0);
        }

        $user->setPrenom($data->prenom);
        $user->setNom($data->nom);
        $user->setEmail($data->email);
//         Hashage du mot de passe
        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $user,
            $data->password
        );
        $user->setPassword($hashedPassword);
        $user->setTelephone($data->telephone);
        $user->setAdresse($data->adresse);
        $user->setCodePostal($data->cp);
        $user->setCommune($data->commune);

        return $user;

    }


    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return User::class === $type;
    }

}