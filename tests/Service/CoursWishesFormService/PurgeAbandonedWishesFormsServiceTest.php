<?php

declare(strict_types=1);

namespace App\Tests\Service\CoursWishesFormService;

use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Enum\StatusCoursWishesFormEnum;
use App\Repository\CoursWishesFormRepository;
use App\Service\CoursWishesFormService\PurgeAbandonedWishesFormsService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(PurgeAbandonedWishesFormsService::class)]
class PurgeAbandonedWishesFormsServiceTest extends TestCase
{
    private CoursWishesFormRepository&MockObject $repository;
    private EntityManagerInterface&MockObject $em;
    private LoggerInterface&MockObject $logger;
    private PurgeAbandonedWishesFormsService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CoursWishesFormRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new PurgeAbandonedWishesFormsService($this->repository, $this->em, $this->logger);
    }

    private function createForm(string $status): CoursWishesForm
    {
        $form = new CoursWishesForm();
        $form->setEmail('jean@example.com');
        $form->setSaison('2026-2027');
        $form->setPackSouhaite(new Pack()->setNom('Pack Test'));
        $form->setModeReglement('CbComptant');
        $form->setStatus($status);
        $form->setSubmittedAt(new \DateTimeImmutable());

        return $form;
    }

    public function testPurgeRemovesExpiredApprovalsAndStaleCorrectionsAndFlushes(): void
    {
        $expired = $this->createForm(StatusCoursWishesFormEnum::VALIDE->value);
        $stale = $this->createForm(StatusCoursWishesFormEnum::A_CORRIGER->value);

        $this->repository->expects($this->once())->method('findExpiredApprovals')->willReturn([$expired]);
        $this->repository->expects($this->once())->method('findStaleCorrections')->willReturn([$stale]);

        $removed = [];
        $this->em->method('remove')->willReturnCallback(function ($form) use (&$removed): void {
            $removed[] = $form;
        });
        $this->em->expects($this->once())->method('flush');
        $this->logger->expects($this->once())->method('info');

        $result = $this->service->purge();

        $this->assertSame([$expired, $stale], $removed);
        $this->assertSame(['expiredApprovals' => 1, 'staleCorrections' => 1], $result);
    }

    public function testPurgeDoesNotFlushWhenNothingToRemove(): void
    {
        $this->repository->expects($this->once())->method('findExpiredApprovals')->willReturn([]);
        $this->repository->expects($this->once())->method('findStaleCorrections')->willReturn([]);

        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('flush');
        $this->logger->expects($this->never())->method('info');

        $result = $this->service->purge();

        $this->assertSame(['expiredApprovals' => 0, 'staleCorrections' => 0], $result);
    }
}
