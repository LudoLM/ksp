<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ArchivageService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserActivityListener implements EventSubscriberInterface
{
    private const int LAST_ACTIVITY_THROTTLE_SECONDS = 1800; // 30 minutes

    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly ArchivageService $archivageService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        // Only track main requests, not subrequests
        if (!$event->isMainRequest()) {
            return;
        }
        $response = $event->getResponse();
        $request = $event->getRequest();

        // Only track successful responses (200)
        if (200 !== $response->getStatusCode()) {
            return;
        }
        // Only track /api/user endpoint
        if ('/api/user' !== $request->getPathInfo()) {
            return;
        }

        // Get authenticated user from security context
        $securityUser = $this->security->getUser();
        if (!$securityUser instanceof User) {
            return;
        }

        // Reuse security user if managed, otherwise fetch from DB (performance optimization)
        $user = $securityUser;
        if (!$this->em->contains($user)) {
            // Only fetch if not already managed by Doctrine
            $user = $this->userRepository->find($securityUser->getId());
            if (!$user instanceof User) {
                return;
            }
        }

        // Throttle: only update if last_visit is older than THROTTLE_SECONDS
        $lastVisit = $user->getLastVisit();
        if ($lastVisit instanceof \DateTimeInterface) {
            $now = new \DateTimeImmutable();
            $diff = $now->getTimestamp() - $lastVisit->getTimestamp();

            if ($diff < self::LAST_ACTIVITY_THROTTLE_SECONDS) {
                return; // Too recent, skip update
            }
        }

        // Update last_visit
        $user->setLastVisit(new \DateTimeImmutable());

        // Auto-unarchive if user is archived
        if ($user->isArchived()) {
            $this->logger->info('User auto-unarchived via activity', [
                'userId' => $user->getId(),
            ]);

            // Unarchive without admin (null = automatic)
            $this->archivageService->unarchiveUser($user);
        }
        $this->em->flush();

        $this->logger->info('User activity tracked', [
            'userId' => $user->getId(),
            'endpoint' => '/api/user',
        ]);
    }
}
