<?php

declare(strict_types=1);

namespace App\Tests\Service\SendingEmail;

use App\Entity\User;
use App\Service\Notification\EmailNotification;
use App\Service\Notification\NotificationManager;
use App\Service\SendingEmail\SendExistingAccountEmailService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SendExistingAccountEmailService::class)]
class SendExistingAccountEmailServiceTest extends TestCase
{
    private NotificationManager&MockObject $notificationManager;
    private SendExistingAccountEmailService $service;

    protected function setUp(): void
    {
        $this->notificationManager = $this->createMock(NotificationManager::class);
        $this->service = new SendExistingAccountEmailService($this->notificationManager, 'https://kine-sport-sante.fr');
    }

    public function testSendBuildsANotificationWithTheLoginLinkAndTheUserAsRecipient(): void
    {
        $user = new User();
        $user->email = 'jean@example.com';
        $user->setPassword('hashed');
        $user->setPrenom('Jean');
        $user->setNom('Dupont');

        $capturedNotification = null;
        $capturedRecipient = null;

        $this->notificationManager
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($notification, $recipient) use (&$capturedNotification, &$capturedRecipient): void {
                $capturedNotification = $notification;
                $capturedRecipient = $recipient;
            });

        $this->service->send($user);

        $this->assertInstanceOf(EmailNotification::class, $capturedNotification);
        $this->assertSame('emails/existingAccountNotice.html.twig', $capturedNotification->getTemplate());
        $this->assertSame($user, $capturedNotification->getParameters()['user']);
        $this->assertSame('https://kine-sport-sante.fr/login', $capturedNotification->getParameters()['loginUrl']);
        $this->assertSame($user, $capturedRecipient);
    }

    public function testSendPropagatesExceptionFromNotificationManager(): void
    {
        $user = new User();
        $user->email = 'jean@example.com';
        $user->setPassword('hashed');

        $this->notificationManager
            ->method('send')
            ->willThrowException(new \RuntimeException('Aucun expéditeur trouvé'));

        $this->expectException(\RuntimeException::class);

        $this->service->send($user);
    }
}
