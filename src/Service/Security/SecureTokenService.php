<?php

declare(strict_types=1);

namespace App\Service\Security;

use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

readonly class SecureTokenService
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
    ) {
    }

    public function generate(): string
    {
        return $this->tokenGenerator->generateToken();
    }

    public function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public function matches(string $token, ?string $storedHash): bool
    {
        return hash_equals($storedHash ?? '', $this->hash($token));
    }
}
