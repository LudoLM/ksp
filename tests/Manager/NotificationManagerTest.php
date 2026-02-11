<?php

namespace App\Tests\Manager;

use App\Service\Interface\Notification\NotificationInterface;
use App\Service\Interface\Notification\NotificationSenderInterface;
use App\Service\Interface\Notification\RecipientInterface;
use App\Service\Notification\NotificationManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NotificationManagerTest extends TestCase
{
    public static function senderSelectionProvider(): \Generator
    {
        yield 'first_sender_supports' => [
            'supportsMap' => [true, false],
            'expectedIndex' => 0,
        ];

        yield 'second_sender_supports' => [
            'supportsMap' => [false, true],
            'expectedIndex' => 1,
        ];
    }

    #[DataProvider('senderSelectionProvider')]
    public function testSendUsesFirstSupportingSender(array $supportsMap, int $expectedIndex): void
    {
        $notification = $this->createMock(NotificationInterface::class);
        $recipient = $this->createMock(RecipientInterface::class);

        $senders = [];
        foreach ($supportsMap as $index => $supports) {
            $sender = $this->createMock(NotificationSenderInterface::class);
            $sender->method('supports')->with($notification)->willReturn($supports);

            if ($index === $expectedIndex) {
                $sender->expects($this->once())->method('send')->with($notification, $recipient);
            } else {
                $sender->expects($this->never())->method('send');
            }

            $senders[] = $sender;
        }

        $manager = new NotificationManager($senders);
        $manager->send($notification, $recipient);
    }

    public function testSendThrowsWhenNoSenderSupports(): void
    {
        $notification = $this->createMock(NotificationInterface::class);
        $notification->method('getType')->willReturn('email');
        $recipient = $this->createMock(RecipientInterface::class);

        $senderA = $this->createMock(NotificationSenderInterface::class);
        $senderA->method('supports')->with($notification)->willReturn(false);
        $senderB = $this->createMock(NotificationSenderInterface::class);
        $senderB->method('supports')->with($notification)->willReturn(false);

        $manager = new NotificationManager([$senderA, $senderB]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Aucun expéditeur trouvé');
        $this->expectExceptionMessage('email');

        $manager->send($notification, $recipient);
    }
}
