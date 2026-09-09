<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Enum\StatusCoursWishesFormEnum;
use App\Message\SendCoursWishesFormStatusEmailMessage;
use App\MessageHandler\SendCoursWishesFormStatusEmailMessageHandler;
use App\Repository\CoursWishesFormRepository;
use App\Service\SendingEmail\SendCoursWishesFormStatusEmailService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SendCoursWishesFormStatusEmailMessageHandler::class)]
class SendCoursWishesFormStatusEmailMessageHandlerTest extends TestCase
{
    private CoursWishesFormRepository&MockObject $repository;
    private SendCoursWishesFormStatusEmailService&MockObject $sendingService;
    private SendCoursWishesFormStatusEmailMessageHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CoursWishesFormRepository::class);
        $this->sendingService = $this->createMock(SendCoursWishesFormStatusEmailService::class);

        $this->handler = new SendCoursWishesFormStatusEmailMessageHandler(
            $this->sendingService,
            $this->repository,
        );
    }

    private function createForm(string $status): CoursWishesForm
    {
        $form = new CoursWishesForm();
        $form->setEmail('jean@example.com');
        $form->setSaison('2026-2027');
        $form->setPackSouhaite(new Pack());
        $form->setModeReglement('CbComptant');
        $form->setStatus($status);
        $form->setSubmittedAt(new \DateTimeImmutable());

        return $form;
    }

    #[DataProvider('statusProvider')]
    public function testInvokeSendsEmailWhenFormExists(string $status): void
    {
        $form = $this->createForm($status);

        $this->repository->expects($this->once())
            ->method('find')
            ->with(42)
            ->willReturn($form);

        $this->sendingService->expects($this->once())
            ->method('send')
            ->with($form, 'raw-token');

        $this->handler->__invoke(new SendCoursWishesFormStatusEmailMessage(42, 'raw-token'));
    }

    public static function statusProvider(): \Generator
    {
        yield 'valide' => ['status' => StatusCoursWishesFormEnum::VALIDE->value];
        yield 'a_corriger' => ['status' => StatusCoursWishesFormEnum::A_CORRIGER->value];
    }

    public function testInvokeThrowsExceptionWhenFormNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->with(404)
            ->willReturn(null);

        $this->sendingService->expects($this->never())
            ->method('send');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Dossier d'inscription non trouvé");

        $this->handler->__invoke(new SendCoursWishesFormStatusEmailMessage(404));
    }

    public function testInvokePropagatesExceptionFromSendingService(): void
    {
        $form = $this->createForm(StatusCoursWishesFormEnum::A_CORRIGER->value);

        $this->repository->method('find')->willReturn($form);

        $this->sendingService
            ->method('send')
            ->willThrowException(new \RuntimeException('Aucun expéditeur trouvé'));

        $this->expectException(\RuntimeException::class);

        $this->handler->__invoke(new SendCoursWishesFormStatusEmailMessage(1));
    }
}
