<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\User;
use App\Message\SendExistingAccountEmailMessage;
use App\MessageHandler\SendExistingAccountEmailMessageHandler;
use App\Repository\UserRepository;
use App\Service\SendingEmail\SendExistingAccountEmailService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SendExistingAccountEmailMessageHandler::class)]
class SendExistingAccountEmailMessageHandlerTest extends TestCase
{
    private UserRepository&MockObject $repository;
    private SendExistingAccountEmailService&MockObject $sendingService;
    private SendExistingAccountEmailMessageHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->sendingService = $this->createMock(SendExistingAccountEmailService::class);

        $this->handler = new SendExistingAccountEmailMessageHandler(
            $this->sendingService,
            $this->repository,
        );
    }

    public function testInvokeSendsEmailWhenUserExists(): void
    {
        $user = new User();
        $user->email = 'jean@example.com';
        $user->setPassword('hashed');

        $this->repository->expects($this->once())
            ->method('find')
            ->with(42)
            ->willReturn($user);

        $this->sendingService->expects($this->once())
            ->method('send')
            ->with($user);

        $this->handler->__invoke(new SendExistingAccountEmailMessage(42));
    }

    public function testInvokeThrowsExceptionWhenUserNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->with(404)
            ->willReturn(null);

        $this->sendingService->expects($this->never())
            ->method('send');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Utilisateur non trouvé');

        $this->handler->__invoke(new SendExistingAccountEmailMessage(404));
    }

    public function testInvokePropagatesExceptionFromSendingService(): void
    {
        $user = new User();
        $user->email = 'jean@example.com';
        $user->setPassword('hashed');

        $this->repository->method('find')->willReturn($user);

        $this->sendingService
            ->method('send')
            ->willThrowException(new \RuntimeException('Aucun expéditeur trouvé'));

        $this->expectException(\RuntimeException::class);

        $this->handler->__invoke(new SendExistingAccountEmailMessage(1));
    }
}
