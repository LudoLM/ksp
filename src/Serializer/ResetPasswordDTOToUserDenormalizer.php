<?php

namespace App\Serializer;

use App\DTO\ResetPasswordDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @method array getSupportedTypes(?string $format)
 */
class ResetPasswordDTOToUserDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        // TODO: Implement denormalize() method.

        if (!$data instanceof ResetPasswordDTO) {
            throw new \Exception('Instance de resetPassword attendue');
        }

        $user = $this->userRepository->find($data->userId);
        if ($data->token === $user->getResetPasswordToken()) {
            $hashedPassword = $this->userPasswordHasher->hashPassword(
                $user,
                $data->password
            );
            $user->setPassword($hashedPassword);
            $user->setResetPasswordToken('');
        } else {
            throw new \Exception('La session de réinitialisation est expirée. Veuillez réessayer.');
        }

        return $user;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null)
    {
        // TODO: Implement supportsDenormalization() method.
        return User::class === $type;
    }
}
