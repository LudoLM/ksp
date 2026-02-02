<?php

namespace App\Serializer;

use App\DTO\ResetPasswordDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ResetPasswordDTOToUserDenormalizer implements DenormalizerInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (!$data instanceof ResetPasswordDTO) {
            throw new \Exception('Instance de resetPassword attendue');
        }

        $user = $this->userRepository->find($data->userId);

        // SÉCURITÉ: Message générique pour empêcher information disclosure
        if (null === $user) {
            throw new \Exception('Le lien de réinitialisation est invalide ou a expiré');
        }

        // 1. Vérifier que le token n'a pas expiré
        if (null === $user->getResetPasswordTokenExpiresAt()
            || $user->getResetPasswordTokenExpiresAt() < new \DateTime()) {
            throw new \Exception('Le lien de réinitialisation est invalide ou a expiré');
        }

        // 2. Vérifier le token avec comparaison constant-time (protection timing attack)
        $tokenHash = hash('sha256', $data->token);
        if (!hash_equals($user->getResetPasswordToken() ?? '', $tokenHash)) {
            throw new \Exception('Le lien de réinitialisation est invalide ou a expiré');
        }

        // 3. Réinitialiser le mot de passe
        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $user,
            $data->password
        );
        $user->setPassword($hashedPassword);

        // 4. Nettoyer le token
        $user->setResetPasswordToken('');
        $user->setResetPasswordTokenExpiresAt(null);

        return $user;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return User::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            User::class => true,
        ];
    }
}
