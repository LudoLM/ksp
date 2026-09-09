<?php

declare(strict_types=1);

namespace App\Tests\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Entity\User;
use App\Enum\StatusCoursWishesFormEnum;
use App\Message\SendCoursWishesFormStatusEmailMessage;
use App\Service\CoursWishesFormService\ValidateCoursWishesFormService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(ValidateCoursWishesFormService::class)]
class ValidateCoursWishesFormServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $em;
    private MessageBusInterface&MockObject $messageBus;
    private ValidateCoursWishesFormService $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->service = new ValidateCoursWishesFormService($this->em, $this->messageBus);

        $this->messageBus->method('dispatch')->willReturn(new Envelope(new \stdClass()));
    }

    private function createForm(int $id = 1): CoursWishesForm
    {
        $form = new CoursWishesForm();
        $form->setEmail('jean@example.com');
        $form->setSaison('2026-2027');
        $form->setPackSouhaite(new Pack());
        $form->setModeReglement('CbComptant');
        $form->setStatus(StatusCoursWishesFormEnum::EN_ATTENTE->value);
        $form->setSubmittedAt(new \DateTimeImmutable());

        $idProp = new \ReflectionClass($form)->getProperty('id');
        $idProp->setValue($form, $id);

        return $form;
    }

    #[DataProvider('actionProvider')]
    public function testUpdateStatusSetsExpectedStateAndDispatchesEmail(
        string $action,
        ?string $reason,
        string $expectedStatus,
        ?string $expectedReason,
    ): void {
        $form = $this->createForm(7);
        $admin = new User();

        $this->em->expects($this->once())->method('flush');

        $dispatchedMessage = null;
        $this->messageBus->method('dispatch')
            ->willReturnCallback(function ($message) use (&$dispatchedMessage): Envelope {
                $dispatchedMessage = $message;

                return new Envelope($message);
            });

        $this->service->updateStatus($form, $action, $admin, $reason);

        $this->assertSame($expectedStatus, $form->getStatus());
        $this->assertSame($expectedReason, $form->getCorrectionReason());
        $this->assertSame($admin, $form->getValidatedBy());
        $this->assertInstanceOf(\DateTimeImmutable::class, $form->getValidatedAt());
        $this->assertInstanceOf(SendCoursWishesFormStatusEmailMessage::class, $dispatchedMessage);
        $this->assertSame(7, $dispatchedMessage->getFormId());
    }

    public static function actionProvider(): \Generator
    {
        yield 'approve' => [
            'action' => 'approve',
            'reason' => null,
            'expectedStatus' => StatusCoursWishesFormEnum::VALIDE->value,
            'expectedReason' => null,
        ];

        yield 'correction' => [
            'action' => 'correction',
            'reason' => 'Créneau primaire déjà complet',
            'expectedStatus' => StatusCoursWishesFormEnum::A_CORRIGER->value,
            'expectedReason' => 'Créneau primaire déjà complet',
        ];
    }
}
