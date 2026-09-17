<?php

declare(strict_types=1);

namespace App\Tests\Service\CoursWishesFormService;

use App\Entity\User;
use App\Message\SendExistingAccountEmailMessage;
use App\Repository\UserRepository;
use App\Service\CoursWishesFormService\CheckExistingAccountService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(CheckExistingAccountService::class)]
class CheckExistingAccountServiceTest extends TestCase
{
    private UserRepository&MockObject $userRepository;
    private MessageBusInterface&MockObject $messageBus;
    private CheckExistingAccountService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->service = new CheckExistingAccountService($this->userRepository, $this->messageBus);
    }

    public function testHandleIfExistsDispatchesNotificationAndReturnsTrueWhenAccountExists(): void
    {
        $user = new User();
        $idProp = new \ReflectionClass($user)->getProperty('id');
        $idProp->setValue($user, 42);

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'jean@example.com'])
            ->willReturn($user);

        $dispatchedMessage = null;
        $this->messageBus->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessage): Envelope {
                $dispatchedMessage = $message;

                return new Envelope($message);
            });

        $result = $this->service->handleIfExists('jean@example.com');

        $this->assertTrue($result);
        $this->assertInstanceOf(SendExistingAccountEmailMessage::class, $dispatchedMessage);
        $this->assertSame(42, $dispatchedMessage->getUserId());
    }

    public function testHandleIfExistsReturnsFalseAndDispatchesNothingWhenNoAccountExists(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'jean@example.com'])
            ->willReturn(null);

        $this->messageBus->expects($this->never())->method('dispatch');

        $result = $this->service->handleIfExists('jean@example.com');

        $this->assertFalse($result);
    }
}
