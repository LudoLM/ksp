<?php

namespace App\Tests\Service\Cours;

use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Enum\StatusCoursEnum;
use App\Repository\StatusCoursRepository;
use App\Service\CoursControllerService\CountUsersInCoursService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CountUsersInCoursServiceTest extends TestCase
{
    public $countUsersInCoursService;
    /* private MockObject&EventDispatcherInterface $dispatcherMock; */
    public MockObject $statusCoursRepositoryMock;

    public function setUp(): void
    {
        $this->statusCoursRepositoryMock = $this->createMock(StatusCoursRepository::class);
        $this->countUsersInCoursService = new CountUsersInCoursService(
            /* $this->dispatcherMock, */
            $this->statusCoursRepositoryMock,
        );
    }

    public static function dataProvider(): \Generator
    {
        yield 'SuccesChangeStatutCours' => [
            'hasCapacityAvailable' => true,
            'actualStatus' => StatusCoursEnum::COMPLET->value,
            'expectAction' => true,
        ];

        yield 'AlreadyOpened' => [
            'hasCapacityAvailable' => true,
            'actualStatus' => StatusCoursEnum::OUVERT->value,
            'expectAction' => false,
        ];

        yield 'AlreadyFulled' => [
            'hasCapacityAvailable' => false,
            'actualStatus' => StatusCoursEnum::COMPLET->value,
            'expectAction' => false,
        ];
    }

    #[DataProvider('dataProvider')]
    public function testreopenIfCapacityAvailable(
        bool $hasCapacityAvailable,
        string $actualStatus,
        bool $expectAction,
    ): void {
        $statusActuel = new StatusCours()->setLibelle($actualStatus);
        $statusOuvert = new StatusCours()->setLibelle(StatusCoursEnum::OUVERT->value);

        $cours = $this->createMock(Cours::class);
        $cours->method('hasCapacityAvailable')->willReturn($hasCapacityAvailable);
        $cours->method('getStatusCours')->willReturn($statusActuel);

        if ($expectAction) {
            $this->statusCoursRepositoryMock
                ->expects($this->once())
                ->method('findOneBy')
                ->with(['libelle' => StatusCoursEnum::OUVERT->value])
                ->willReturn($statusOuvert);

            $cours->expects($this->once())
                ->method('setStatusCours')
                ->with($statusOuvert);

        /* $this->dispatcherMock->expects($this->once())->method('dispatch'); */
        } else {
            $this->statusCoursRepositoryMock->expects($this->never())->method('findOneBy');
            $cours->expects($this->never())->method('setStatusCours');
            /* $this->dispatcherMock->expects($this->never())->method('dispatch'); */
        }

        // ACT
        $this->countUsersInCoursService->reopenIfCapacityAvailable($cours);
    }
}
