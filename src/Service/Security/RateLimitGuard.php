<?php

declare(strict_types=1);

namespace App\Service\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final readonly class RateLimitGuard
{
    public function checkOrRespond(RateLimiterFactory $limiterFactory, string $identifier, string $message): ?JsonResponse
    {
        if ($this->isAllowed($limiterFactory, $identifier)) {
            return null;
        }

        return new JsonResponse(['type' => 'error', 'message' => $message], Response::HTTP_TOO_MANY_REQUESTS);
    }

    public function checkOrRespondWithErrorShape(RateLimiterFactory $limiterFactory, string $identifier, string $message): ?JsonResponse
    {
        if ($this->isAllowed($limiterFactory, $identifier)) {
            return null;
        }

        return new JsonResponse(['error' => $message], Response::HTTP_TOO_MANY_REQUESTS);
    }

    private function isAllowed(RateLimiterFactory $limiterFactory, string $identifier): bool
    {
        return $limiterFactory->create($identifier)->consume(1)->isAccepted();
    }
}
