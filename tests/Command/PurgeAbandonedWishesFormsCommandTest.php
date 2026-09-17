<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\PurgeAbandonedWishesFormsCommand;
use App\Entity\CoursWishesForm;
use App\Entity\Pack;
use App\Enum\StatusCoursWishesFormEnum;
use App\Repository\CoursWishesFormRepository;
use App\Service\CoursWishesFormService\PurgeAbandonedWishesFormsService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(PurgeAbandonedWishesFormsCommand::class)]
class PurgeAbandonedWishesFormsCommandTest extends TestCase
{
    private PurgeAbandonedWishesFormsService&MockObject $purgeService;
    private CoursWishesFormRepository&MockObject $repository;
    private EntityManagerInterface&MockObject $em;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->purgeService = $this->createMock(PurgeAbandonedWishesFormsService::class);
        $this->repository = $this->createMock(CoursWishesFormRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        $command = new PurgeAbandonedWishesFormsCommand($this->purgeService, $this->repository, $this->em);
        $this->commandTester = new CommandTester($command);
    }

    private function createForm(int $id, string $status): CoursWishesForm
    {
        $form = new CoursWishesForm();
        $form->setEmail('jean@example.com');
        $form->setSaison('2026-2027');
        $form->setPackSouhaite(new Pack()->setNom('Pack Test'));
        $form->setModeReglement('CbComptant');
        $form->setStatus($status);
        $form->setSubmittedAt(new \DateTimeImmutable());

        $idProp = new \ReflectionClass($form)->getProperty('id');
        $idProp->setValue($form, $id);

        return $form;
    }

    public function testDryRunDisplaysBothCategoriesWithoutModifyingAnything(): void
    {
        $this->repository->expects($this->once())
            ->method('findExpiredApprovals')
            ->willReturn([$this->createForm(1, StatusCoursWishesFormEnum::VALIDE->value)]);
        $this->repository->expects($this->once())
            ->method('findStaleCorrections')
            ->willReturn([$this->createForm(2, StatusCoursWishesFormEnum::A_CORRIGER->value)]);

        $this->purgeService->expects($this->never())->method('purge');
        $this->em->expects($this->never())->method('beginTransaction');

        $exitCode = $this->commandTester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('jean@example.com', $this->commandTester->getDisplay());
    }

    public function testDryRunReportsWhenNothingToPurge(): void
    {
        $this->repository->method('findExpiredApprovals')->willReturn([]);
        $this->repository->method('findStaleCorrections')->willReturn([]);

        $exitCode = $this->commandTester->execute(['--dry-run' => true]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Aucun dossier', $this->commandTester->getDisplay());
    }

    public function testExecutePurgesWithinATransaction(): void
    {
        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('commit');
        $this->em->expects($this->never())->method('rollback');

        $this->purgeService->expects($this->once())
            ->method('purge')
            ->willReturn(['expiredApprovals' => 2, 'staleCorrections' => 1]);

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('2 dossier(s) validé(s) expiré(s) supprimé(s)', $display);
        $this->assertStringContainsString('1 dossier(s) en correction', $display);
    }

    public function testExecuteRollsBackOnFailure(): void
    {
        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('rollback');
        $this->em->expects($this->never())->method('commit');

        $this->purgeService->method('purge')
            ->willThrowException(new \RuntimeException('DB down'));

        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('DB down', $this->commandTester->getDisplay());
    }
}
