<?php

namespace App\Tests\Command;

use App\Command\SendToUsersCoursAvailabiltyCommand;
use App\Message\SendToUsersCoursAvailabilityMessage;
use App\Repository\CoursRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(SendToUsersCoursAvailabiltyCommand::class)]
class SendToUsersCoursAvailabiltyCommandTest extends TestCase
{
    private CoursRepository&MockObject $coursRepository;
    private UserRepository&MockObject $userRepository;
    private MessageBusInterface&MockObject $messageBus;
    private SendToUsersCoursAvailabiltyCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->coursRepository = $this->createMock(CoursRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->command = new SendToUsersCoursAvailabiltyCommand(
            $this->coursRepository,
            $this->userRepository,
            $this->messageBus,
        );

        $this->commandTester = new CommandTester($this->command);
    }

    #[DataProvider('executeDataProvider')]
    public function testExecuteDispatchesMessagesForEachUser(
        array $userIds,
        array $coursIds,
        int $expectedMessageCount,
    ): void {
        $this->userRepository->expects($this->once())
            ->method('findIdsActiveUsers')
            ->willReturn($userIds);

        $this->coursRepository->expects($this->once())
            ->method('findIdsOpenCoursForNextWeek')
            ->willReturn($coursIds);

        $dispatchedMessages = [];
        $this->messageBus->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessages): Envelope {
                $dispatchedMessages[] = $message;

                return new Envelope($message);
            });

        $statusCode = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $statusCode);
        $this->assertStringContainsString('Mails envoyés', $this->commandTester->getDisplay());
        $this->assertCount($expectedMessageCount, $dispatchedMessages);

        foreach ($dispatchedMessages as $index => $message) {
            $this->assertInstanceOf(SendToUsersCoursAvailabilityMessage::class, $message);
            $this->assertSame($userIds[$index], $message->getUserId());
            $this->assertSame($coursIds, $message->getCoursAvailabilities());
        }
    }

    public function testExecuteReturnsFailureOnException(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findIdsActiveUsers')
            ->willThrowException(new \Exception('Database error'));

        $this->messageBus->expects($this->never())
            ->method('dispatch');

        $statusCode = $this->commandTester->execute([]);

        $this->assertEquals(Command::FAILURE, $statusCode);
        $this->assertStringContainsString('Erreur', $this->commandTester->getDisplay());
    }

    public static function executeDataProvider(): \Generator
    {
        yield 'single_user_single_cours' => [
            'userIds' => [1],
            'coursIds' => [100],
            'expectedMessageCount' => 1,
        ];

        yield 'multiple_users_multiple_cours' => [
            'userIds' => [1, 2, 3],
            'coursIds' => [100, 101, 102],
            'expectedMessageCount' => 3,
        ];

        yield 'no_users' => [
            'userIds' => [],
            'coursIds' => [100],
            'expectedMessageCount' => 0,
        ];

        yield 'no_cours' => [
            'userIds' => [1, 2],
            'coursIds' => [],
            'expectedMessageCount' => 2,
        ];
    }
}
