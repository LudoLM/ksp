<?php

namespace App\Tests;

    use App\Exception\FilteringCoursException;
    use App\Repository\CoursRepository;
    use App\Repository\StatusCoursRepository;
    use App\Repository\TypeCoursRepository;
    use App\Service\CoursControllerService\FilteringCoursService;
    use PHPUnit\Framework\TestCase;

    class FilteringCoursTest extends TestCase
{
    private $coursRepository;
    private $typeCoursRepository;
    private $statusCoursRepository;
    private $filteringCoursService;

    protected function setUp(): void
    {
        // Mock des repositories
        $this->coursRepository = $this->createMock(CoursRepository::class);
        $this->typeCoursRepository = $this->createMock(TypeCoursRepository::class);
        $this->statusCoursRepository = $this->createMock(StatusCoursRepository::class);

        // Instancier le service avec les mocks
        $this->filteringCoursService = new FilteringCoursService(
            $this->coursRepository,
            $this->typeCoursRepository,
            $this->statusCoursRepository
        );
    }

    public function testFilterCoursReturnsExpectedData(): void
    {
        // Arrange
        $currentPage = 1;
        $maxPerPage = 10;
        $typeCoursId = 1;
        $dateCoursStr = '2025-02-24';
        $statusCoursId = 2;
        $isAdminPath = true;
        $route = 'api_cours_list';

        // Mock pour TypeCours
        $typeCoursMock = new \stdClass(); // Simule une entité TypeCours
        $this->typeCoursRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $typeCoursId])
            ->willReturn($typeCoursMock);

        // Mock pour StatusCours
        $statusCoursMock = new \stdClass(); // Simule une entité StatusCours
        $this->statusCoursRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $statusCoursId])
            ->willReturn($statusCoursMock);

        // Mock pour CoursRepository
        $coursPaginator = new \ArrayIterator(['cours1', 'cours2']); // Simule les résultats de la pagination
        $this->coursRepository
            ->expects($this->once())
            ->method('findAllSortByDate')
            ->with($currentPage, $maxPerPage, $typeCoursMock, new \DateTime($dateCoursStr), null, $statusCoursMock)
            ->willReturn($coursPaginator);

        // Act
        $result = $this->filteringCoursService->filterCours(
            $currentPage,
            $maxPerPage,
            $typeCoursId,
            $dateCoursStr,
            $statusCoursId,
            $isAdminPath,
            $route
        );

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertCount(2, $result['data']); // Vérifie que deux cours sont retournés
    }

    public function testFilterCoursThrowsExceptionForInvalidDate(): void
    {
        // Arrange
        $currentPage = 1;
        $maxPerPage = 10;
        $typeCoursId = 1;
        $dateCoursStr = 'invalid-date'; // Date invalide
        $statusCoursId = 2;
        $isAdminPath = true;
        $route = 'api_cours_list';

        // Assert : S'attendre à une exception FilteringCoursException
        $this->expectException(FilteringCoursException::class);
        $this->expectExceptionMessage("La date fournie est invalide");

        // Act
        $this->filteringCoursService->filterCours(
            $currentPage,
            $maxPerPage,
            $typeCoursId,
            $dateCoursStr,
            $statusCoursId,
            $isAdminPath,
            $route
        );
    }

    public function testFilterCoursHandlesNullTypeCours(): void
    {
        // Arrange
        $currentPage = 1;
        $maxPerPage = 10;
        $typeCoursId = 0; // Pas de TypeCours
        $dateCoursStr = '2025-02-24';
        $statusCoursId = 2;
        $isAdminPath = false;
        $route = 'api_cours_list';

        // Mock pour CoursRepository
        $coursPaginator = new \ArrayIterator(['cours1', 'cours2']);
        $this->coursRepository
            ->expects($this->once())
            ->method('findAllSortByDateForUsers')
            ->with($currentPage, $maxPerPage, null, new \DateTime($dateCoursStr), null, null)
            ->willReturn($coursPaginator);

        // Act
        $result = $this->filteringCoursService->filterCours(
            $currentPage,
            $maxPerPage,
            $typeCoursId,
            $dateCoursStr,
            $statusCoursId,
            $isAdminPath,
            $route
        );

        // Assert
        $this->assertCount(2, $result['data']);
    }
}

