<?php

namespace App\Tests\Service\Cours;

use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Enum\StatusCoursEnum;
use App\Helper\UpdateStatusCoursHelper;
use App\Repository\StatusCoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(UpdateStatusCoursHelper::class)]
class UpdateStatusCoursHelperTest extends TestCase
{
    private StatusCoursRepository $statusCoursRepository;
    private UpdateStatusCoursHelper $updateStatusCoursHelper;
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $messageBus;

    public function setUp(
    ): void {
        $this->statusCoursRepository = $this->createMock(StatusCoursRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->updateStatusCoursHelper = new UpdateStatusCoursHelper(
            $this->messageBus,
            $this->statusCoursRepository,
            $this->entityManager,
        );
    }

    public static function dataProvider(): \Generator
    {
        yield 'Ouvert_to_EnCours' => [
            'initialStatus' => StatusCoursEnum::OUVERT,
            'expectedStatus' => StatusCoursEnum::EN_COURS,
        ];

        yield 'EnCours_to_Passé' => [
            'initialStatus' => StatusCoursEnum::EN_COURS,
            'expectedStatus' => StatusCoursEnum::PASSE,
        ];

        yield 'EnCours_to_EnCours' => [
            'initialStatus' => StatusCoursEnum::EN_COURS,
            'expectedStatus' => StatusCoursEnum::EN_COURS,
        ];

        yield 'Passé_to_Archive' => [
            'initialStatus' => StatusCoursEnum::PASSE,
            'expectedStatus' => StatusCoursEnum::ARCHIVE,
        ];

        yield 'WrongDate_no_change' => [
            'initialStatus' => StatusCoursEnum::OUVERT,
            'expectedStatus' => StatusCoursEnum::OUVERT,
        ];
    }

    #[DataProvider('dataProvider')]
    public function testSetNewStatus(StatusCoursEnum $initialStatus, StatusCoursEnum $expectedStatus): void
    {
        // Mock du repository

        $expectedStatusCours = new StatusCours();
        $expectedStatusCours->setLibelle($expectedStatus->value);

        // Mock du repository
        $this->statusCoursRepository
            ->method('findOneBy')
            ->willReturn($expectedStatusCours);
        // Mock du Cours
        $cours = new Cours();

        $this->updateStatusCoursHelper->setNewStatus($cours, $initialStatus);

        $this->assertEquals($expectedStatus->value, $cours->getStatusCours()->getLibelle());
    }
}
