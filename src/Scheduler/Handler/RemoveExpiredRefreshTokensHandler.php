<?php

namespace App\Scheduler\Handler;

use App\Scheduler\RemoveExpiredRefreshTokens;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemoveExpiredRefreshTokensHandler
{
    public function __construct(
        private RefreshTokenManagerInterface $refreshTokenManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        RemoveExpiredRefreshTokens $message,
    ): void {
        try {
            $deleted = $this->refreshTokenManager->revokeAllInvalid();
            $this->logger->info('Suppression des tokens expirés terminée. Nombre de tokens supprimés :'.count($deleted));
        } catch (\Exception $exception) {
            $this->logger->error('Erreur lors de la suppression des tokens expirés : '.$exception->getMessage());
        }
    }
}
