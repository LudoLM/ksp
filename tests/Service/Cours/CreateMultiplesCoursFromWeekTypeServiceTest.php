<?php

namespace App\Tests\Service\Cours;

use App\DTO\AssignWeekDTO;
use App\DTO\CoursSnapshotItemDTO; // Assurez-vous d'importer le DTO imbriqué
use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Entity\TypeCours;
use App\Entity\WeekType;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Repository\WeekTypeRepository;
use App\Service\CreateMultiplesCoursFromWeekTypeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateMultiplesCoursFromWeekTypeServiceTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $weekTypeRepository;
    private \PHPUnit\Framework\MockObject\MockObject $typeCoursRepository;
    private \PHPUnit\Framework\MockObject\MockObject $statusCoursRepository;
    private \PHPUnit\Framework\MockObject\MockObject $entityManager;
    private CreateMultiplesCoursFromWeekTypeService $service;

    protected function setUp(): void
    {
        $this->weekTypeRepository = $this->createMock(WeekTypeRepository::class);
        $this->typeCoursRepository = $this->createMock(TypeCoursRepository::class);
        $this->statusCoursRepository = $this->createMock(StatusCoursRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->service = new CreateMultiplesCoursFromWeekTypeService(
            $this->weekTypeRepository,
            $this->typeCoursRepository,
            $this->statusCoursRepository,
            $this->entityManager
        );
    }

    public static function provideCours(): \Generator
    {
        // Cas simple : un seul cours le lundi à 9h
        yield '1 cours simple' => [
            [[
                'daySelected' => 0,
                'timeSelected' => '09:00',
                'duree' => 60,
                'nbInscriptionMax' => 10,
                'specialNote' => 'note',
                'hasPriority' => true,
                'hasLimitOfOneCoursPerWeek' => false,
                'typeCours' => ['id' => 1],
            ]],
            1, // on s’attend à 1 persist()
            [1], // on doit appeler TypeCoursRepository::find(1)
        ];

        // Cas avec 2 cours dans la même semaine : lundi matin + mercredi après-midi
        yield '2 cours' => [
            [
                [
                    'daySelected' => 0, // lundi
                    'timeSelected' => '09:00',
                    'duree' => 60,
                    'nbInscriptionMax' => 10,
                    'specialNote' => 'note1',
                    'hasPriority' => true,
                    'hasLimitOfOneCoursPerWeek' => false,
                    'typeCours' => ['id' => 1],
                ],
                [
                    'daySelected' => 2, // mercredi
                    'timeSelected' => '14:30',
                    'duree' => 90,
                    'nbInscriptionMax' => 15,
                    'specialNote' => 'note2',
                    'hasPriority' => false,
                    'hasLimitOfOneCoursPerWeek' => true,
                    'typeCours' => ['id' => 2],
                ],
            ],
            2, // on s’attend à 2 persist()
            [1, 2], // TypeCoursRepository::find doit être appelé pour id=1 et id=2
        ];

        // Cas limite : aucun cours dans la semaine → pas de persist
        yield 'aucun cours' => [
            [],
            0, // aucun persist attendu
            [], // aucun appel à TypeCoursRepository
        ];
    }

    private function makeAssignWeekDTO(array $data): AssignWeekDTO
    {
        $dto = new AssignWeekDTO();
        $dto->weekTypeId = 1; // Ou passer en paramètre si weekTypeId varie
        $dto->dateMonday = '2025-10-20'; // Ou passer en paramètre si dateMonday varie
        $dto->coursList = array_map(function ($cours): CoursSnapshotItemDTO {
            $coursDto = new CoursSnapshotItemDTO();
            // Assurez-vous d'attribuer explicitement toutes les propriétés attendues
            $coursDto->daySelected = $cours['daySelected'] ?? 0;
            $coursDto->timeSelected = $cours['timeSelected'] ?? '00:00';
            $coursDto->duree = $cours['duree'] ?? 0;
            $coursDto->nbInscriptionMax = $cours['nbInscriptionMax'] ?? 0;
            $coursDto->specialNote = $cours['specialNote'] ?? null;
            $coursDto->hasPriority = $cours['hasPriority'] ?? false;
            $coursDto->hasLimitOfOneCoursPerWeek = $cours['hasLimitOfOneCoursPerWeek'] ?? false;
            $coursDto->typeCours = $cours['typeCours'] ?? ['id' => 0]; // Assurez une structure par défaut

            return $coursDto;
        }, $data);

        return $dto;
    }

    /**
     * @dataProvider provideCours
     */
    public function testCreateSuccess(array $data, int $expectedPersistCalls, array $typeCoursIds): void
    {
        $assignWeekDTO = $this->makeAssignWeekDTO($data);

        $this->weekTypeRepository->method('find')->willReturn($this->createMock(WeekType::class));
        $this->statusCoursRepository->method('findOneBy')->willReturn($this->createMock(StatusCours::class));

        $map = array_map(fn ($id): array => [$id, $this->createMock(TypeCours::class)], $typeCoursIds);
        $this->typeCoursRepository->expects($this->exactly($expectedPersistCalls))->method('find')->willReturnMap($map);

        $this->entityManager->expects($this->exactly($expectedPersistCalls))->method('persist')->with($this->isInstanceOf(Cours::class));
        $this->entityManager->expects($this->once())->method('flush');

        $this->service->create($assignWeekDTO);
    }

    public function testThrowsIfWeekTypeNotFound(): void
    {
        $dto = $this->makeAssignWeekDTO([]);
        $this->weekTypeRepository->method('find')->willReturn(null);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('WeekType');
        $this->service->create($dto);
    }

    public function testThrowsIfStatusNotFound(): void
    {
        $dto = $this->makeAssignWeekDTO([['daySelected' => 0, 'timeSelected' => '09:00', 'duree' => 60, 'nbInscriptionMax' => 10, 'typeCours' => ['id' => 1]]]);

        $this->weekTypeRepository->method('find')->willReturn($this->createMock(WeekType::class));
        $this->statusCoursRepository->method('findOneBy')->willReturn(null);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('EN_CREATION');
        $this->service->create($dto);
    }

    public function testThrowsIfTypeCoursNotFound(): void
    {
        $dto = $this->makeAssignWeekDTO([
            ['daySelected' => 0, 'timeSelected' => '09:00', 'duree' => 60, 'nbInscriptionMax' => 10, 'typeCours' => ['id' => 999]],
        ]);
        $this->weekTypeRepository->method('find')->willReturn($this->createMock(WeekType::class));
        $this->statusCoursRepository->method('findOneBy')->willReturn($this->createMock(StatusCours::class));
        $this->typeCoursRepository->method('find')->willReturn(null);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('999');
        $this->service->create($dto);
    }

    public function testThrowsIfTimeInvalid(): void
    {
        $dto = $this->makeAssignWeekDTO([
            ['daySelected' => 0, 'timeSelected' => 'invalid', 'duree' => 60, 'nbInscriptionMax' => 10, 'typeCours' => ['id' => 1]],
        ]);

        $this->weekTypeRepository->method('find')->willReturn($this->createMock(WeekType::class));
        $this->statusCoursRepository->method('findOneBy')->willReturn($this->createMock(StatusCours::class));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('heure');
        $this->service->create($dto);
    }
}
