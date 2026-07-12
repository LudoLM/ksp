<?php

namespace App\Tests\Service\SendingEmail;

use App\DTO\CoursAvailabilityDTO;
use App\DTO\UserContactDTO;
use App\Enum\CoursEnum;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;
use App\Service\SendingEmail\SendToUsersCoursAvailabilityService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SendToUsersCoursAvailabilityService::class)]
class SendToUsersCoursAvailabilityServiceTest extends TestCase
{
    private NotificationManager&MockObject $notificationManager;
    private SendToUsersCoursAvailabilityService $service;

    protected function setUp(): void
    {
        $this->notificationManager = $this->createMock(NotificationManager::class);
        $this->service = new SendToUsersCoursAvailabilityService(
            $this->notificationManager,
            'https://example.com'
        );
    }

    #[DataProvider('sendCourseAvailabilityProvider')]
    public function testSendCallsNotificationManagerWithCorrectData(
        UserContactDTO $user,
        array $cours,
    ): void {
        $capturedNotification = null;
        $capturedUser = null;

        $this->notificationManager
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($notification, $userArg) use (&$capturedNotification, &$capturedUser): void {
                $capturedNotification = $notification;
                $capturedUser = $userArg;
            });

        $this->service->send($user, $cours);

        $this->assertInstanceOf(EmailNotification::class, $capturedNotification);
        $this->assertSame('Disponibilités de la semaine', $capturedNotification->getSubject());
        $this->assertSame('emails/coursAvailability.html.twig', $capturedNotification->getTemplate());
        $this->assertSame($user, $capturedNotification->getParameters()['user']);
        $this->assertSame($cours, $capturedNotification->getParameters()['coursAvailabilities']);
        $this->assertSame('https://example.com', $capturedNotification->getParameters()['url']);
        $this->assertSame($user, $capturedUser);
    }

    public function testSendThrowsRuntimeExceptionWhenNoSenderSupportsNotification(): void
    {
        $this->notificationManager
            ->method('send')
            ->willThrowException(new \RuntimeException('Aucun expéditeur trouvé'));

        $this->expectException(\RuntimeException::class);

        $this->service->send(
            new UserContactDTO(1, 'Jean', 'Dupont', 'jean@example.com', '0123456789'),
            []
        );
    }

    public static function sendCourseAvailabilityProvider(): \Generator
    {
        yield 'single_course' => [
            'user' => new UserContactDTO(1, 'Jean', 'Dupont', 'jean@example.com', '0123456789'),
            'cours' => [
                new CoursAvailabilityDTO(1, CoursEnum::PILATES_DEBUTANT->getValue(), new \DateTimeImmutable('2024-01-15'), 60),
            ],
        ];

        yield 'multiple_courses' => [
            'user' => new UserContactDTO(2, 'Marie', 'Martin', 'marie@example.com', '0987654321'),
            'cours' => [
                new CoursAvailabilityDTO(1, CoursEnum::PILATES_DEBUTANT->getValue(), new \DateTimeImmutable('2024-01-15'), 60),
                new CoursAvailabilityDTO(2, CoursEnum::CIRCUIT_TRAINING->getValue(), new \DateTimeImmutable('2024-01-16'), 45),
                new CoursAvailabilityDTO(3, CoursEnum::GYM_DOUCE->getValue(), new \DateTimeImmutable('2024-01-17'), 50),
            ],
        ];

        yield 'empty_courses' => [
            'user' => new UserContactDTO(3, 'Pierre', 'Durand', 'pierre@example.com', '0111111111'),
            'cours' => [],
        ];
    }
}
