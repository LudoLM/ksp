<?php

namespace App\Tests\Service\Cours;

use App\Entity\Cours;
use App\Entity\StatusCours;
use App\Entity\TypeCours;
use App\Exception\FilteringCoursException;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Service\CoursControllerService\FilteringCoursService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FilteringCoursServiceTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $coursRepository;
    private \PHPUnit\Framework\MockObject\MockObject $typeCoursRepository;
    private \PHPUnit\Framework\MockObject\MockObject $statusCoursRepository;
    private FilteringCoursService $filteringCoursService;

    protected function setUp(): void
    {
        $this->coursRepository = $this->createMock(CoursRepository::class);
        $this->typeCoursRepository = $this->createMock(TypeCoursRepository::class);
        $this->statusCoursRepository = $this->createMock(StatusCoursRepository::class);
        $this->filteringCoursService = new FilteringCoursService(
            $this->coursRepository,
            $this->typeCoursRepository,
            $this->statusCoursRepository
        );
    }

    public static function filterProvider(): \Generator
    {
        $dateLimit = new \DateTime('2023-11-05T00:00:00.000000+0000');

        $cours1 = new Cours();
        $cours1->setTypeCours(new TypeCours());
        $cours1->setDateCours(new \DateTime('2023-10-31T00:00:00.000000+0000'));
        $cours1->setStatusCours(new StatusCours());
        $cours1->setDuree(60);
        $cours1->setNbInscriptionMax(12);
        $cours1->setSpecialNote('');

        yield 'cours_calendar_routes' => [
            'route' => 'cours_calendar',
            'isOpenRequired' => false,
            'expectedCallRepository' => 'findAllSortByDateForUsers',
            'dateLimit' => $dateLimit,
            'cours' => [
                $cours1,
            ],
        ];

        $dateLimit2 = new \DateTime('2023-12-01T00:00:00.000000+0000');

        yield 'api_cours_list_routes' => [
            'route' => 'api_cours_list',
            'isOpenRequired' => true,
            'expectedCallRepository' => 'findAllSortByDate',
            'dateLimit' => $dateLimit2,
            'cours' => [
                $cours1,
            ],
        ];
    }

    #[DataProvider('filterProvider')]
    public function testFilterCoursWithValidData(string $route, bool $isOpenRequired, string $expectedCallRepository, \DateTime $dateLimit, array $cours): void
    {
        $typeCoursId = 1;
        $statusCoursId = 1;
        $dateCoursStr = '2023-11-01';
        $typeCours = new TypeCours();
        $statusCours = new StatusCours();

        $this->typeCoursRepository->method('findOneBy')->with(['id' => $typeCoursId])->willReturn($typeCours);
        $this->statusCoursRepository->method('findOneBy')->with(['id' => $statusCoursId])->willReturn($statusCours);
        $dateCours = 'findAllSortByDateForUsers' === $expectedCallRepository ? new \DateTime('2023-10-30T00:00:00.000000+0000') : new \DateTime('2023-11-01T00:00:00.000000+0000');
        $this->coursRepository->method($expectedCallRepository)->with($typeCours, $dateCours, $dateLimit)->willReturn($cours);

        $responseData = $this->filteringCoursService->filterCours($typeCoursId, $dateCoursStr, $statusCoursId, $route, $isOpenRequired);

        $this->assertEquals($cours, $responseData);
    }

    public function testFilterCoursWithInvalidDate(): void
    {
        $typeCoursId = 1; // ID valide
        $dateCoursStr = 'invalid-date'; // Date invalide
        $statusCoursId = 1;
        $route = 'cours_calendar';
        $isOpenRequired = false;

        // Retourner un TypeCours valide pour l'ID fourni
        $this->typeCoursRepository->method('findOneBy')->with(['id' => $typeCoursId])->willReturn(new TypeCours());

        // Attendre l'exception spécifique avec le message et le code appropriés
        $this->expectException(FilteringCoursException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('La date fournie est invalide');

        // Appeler la méthode qui devrait lever l'exception pour la date invalide
        $this->filteringCoursService->filterCours($typeCoursId, $dateCoursStr, $statusCoursId, $route, $isOpenRequired);
    }

    public function testFilterCoursWithTypeCoursNotFound(): void
    {
        $typeCoursId = 999; // ID inexistant
        $dateCoursStr = '2023-10-01';
        $statusCoursId = 1;
        $route = 'cours_calendar';
        $isOpenRequired = false;

        // Configurer le mock pour retourner null lorsque l'ID n'existe pas
        $this->typeCoursRepository->method('findOneBy')->with(['id' => $typeCoursId])->willReturn(null);

        // Attendre l'exception spécifique avec le message et le code appropriés
        $this->expectException(FilteringCoursException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Le type de cours fourni est invalide');

        // Appeler la méthode qui devrait lever l'exception
        $this->filteringCoursService->filterCours($typeCoursId, $dateCoursStr, $statusCoursId, $route, $isOpenRequired);
    }
}
