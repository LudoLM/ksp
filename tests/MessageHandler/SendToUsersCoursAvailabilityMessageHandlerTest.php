<?php

namespace App\Tests\MessageHandler;

use App\DTO\CoursAvailabilityDTO;
use App\DTO\UserContactDTO;
use App\Enum\CoursEnum;
use App\Message\SendToUsersCoursAvailabilityMessage;
use App\MessageHandler\SendToUsersCoursAvailabilityMessageHandler;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use App\Service\SendingEmail\SendToUsersCoursAvailabilityService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(SendToUsersCoursAvailabilityMessageHandler::class)]
class SendToUsersCoursAvailabilityMessageHandlerTest extends TestCase
{
    private UserRepository&MockObject $userRepository;
    private CoursRepository&MockObject $coursRepository;
    private LoggerInterface&MockObject $logger;
    private SendToUsersCoursAvailabilityService&MockObject $sendingService;
    private SendToUsersCoursAvailabilityMessageHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->coursRepository = $this->createMock(CoursRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sendingService = $this->createMock(SendToUsersCoursAvailabilityService::class);

        $this->handler = new SendToUsersCoursAvailabilityMessageHandler(
            $this->userRepository,
            $this->coursRepository,
            $this->logger,
            $this->sendingService,
        );
    }

    #[DataProvider('validCoursProvider')]
    public function testInvokeDispatchesNotificationWhenUserAndCoursExist(
        int $userId,
        array $coursIds,
        UserContactDTO $user,
        array $cours,
    ): void {
        $this->userRepository->expects($this->once())
            ->method('findUserContact')
            ->with($userId)
            ->willReturn($user);

        $this->coursRepository->expects($this->once())
            ->method('findCoursAvailabilitiesByIds')
            ->with($coursIds)
            ->willReturn($cours);

        $this->sendingService->expects($this->once())
            ->method('send')
            ->with($user, $cours);

        $message = new SendToUsersCoursAvailabilityMessage($userId, $coursIds);
        $this->handler->__invoke($message);
    }

    public static function validCoursProvider(): \Generator
    {
        yield 'single_cours' => [
            'userId' => 1,
            'coursIds' => [100],
            'user' => new UserContactDTO(1, 'Jean', 'Dupont', 'jean@example.com', '0123456789'),
            'cours' => [
                new CoursAvailabilityDTO(1, CoursEnum::GYM_DOUCE->getValue(), new \DateTimeImmutable('2024-01-15'), 60),
            ],
        ];

        yield 'multiple_cours' => [
            'userId' => 2,
            'coursIds' => [100, 101, 102],
            'user' => new UserContactDTO(2, 'Marie', 'Martin', 'marie@example.com', '0987654321'),
            'cours' => [
                new CoursAvailabilityDTO(1, CoursEnum::GYM_DOUCE->getValue(), new \DateTimeImmutable('2024-01-15'), 60),
                new CoursAvailabilityDTO(2, CoursEnum::CIRCUIT_TRAINING->getValue(), new \DateTimeImmutable('2024-01-16'), 45),
                new CoursAvailabilityDTO(3, CoursEnum::PILATES_DEBUTANT->getValue(), new \DateTimeImmutable('2024-01-17'), 50),
            ],
        ];
    }

    public function testInvokeThrowsExceptionWhenUserNotFound(): void
    {
        $userId = 999;
        $coursIds = [100];

        $this->userRepository->expects($this->once())
            ->method('findUserContact')
            ->with($userId)
            ->willReturn(null);

        $this->coursRepository->expects($this->never())
            ->method('findCoursAvailabilitiesByIds');

        $this->sendingService->expects($this->never())
            ->method('send');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Utilisateur non trouvé');

        $message = new SendToUsersCoursAvailabilityMessage($userId, $coursIds);
        $this->handler->__invoke($message);
    }

    public function testInvokeLogsAndStopsWhenNoCours(): void
    {
        $userId = 1;
        $coursIds = [999];

        $user = new UserContactDTO(1, 'Jean', 'Dupont', 'jean@example.com', '0123456789');

        $this->userRepository->expects($this->once())
            ->method('findUserContact')
            ->with($userId)
            ->willReturn($user);

        $this->coursRepository->expects($this->once())
            ->method('findCoursAvailabilitiesByIds')
            ->with($coursIds)
            ->willReturn([]);

        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Aucun cours disponible'));

        $this->sendingService->expects($this->never())
            ->method('send');

        $message = new SendToUsersCoursAvailabilityMessage($userId, $coursIds);
        $this->handler->__invoke($message);
    }

    public function testInvokePropagatesExceptionFromSendingService(): void
    {
        $userId = 1;
        $coursIds = [100];
        $user = new UserContactDTO(1, 'Jean', 'Dupont', 'jean@example.com', '0123456789');
        $cours = [
            new CoursAvailabilityDTO(1, CoursEnum::GYM_DOUCE->getValue(), new \DateTimeImmutable('2024-01-15'), 60),
        ];

        $this->userRepository->method('findUserContact')->willReturn($user);
        $this->coursRepository->method('findCoursAvailabilitiesByIds')->willReturn($cours);

        $this->sendingService
            ->method('send')
            ->willThrowException(new \RuntimeException('Aucun expéditeur trouvé'));

        $this->expectException(\RuntimeException::class);

        $message = new SendToUsersCoursAvailabilityMessage($userId, $coursIds);
        $this->handler->__invoke($message);
    }
}
