<?php

namespace App\Tests\Service;

use App\Entity\StatusCours;
use App\Entity\TypeCours;
use App\Exception\FilteringCoursException;
use App\Repository\CoursRepository;
use App\Repository\StatusCoursRepository;
use App\Repository\TypeCoursRepository;
use App\Service\CoursControllerService\FilteringCoursService;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\Tools\Pagination\Paginator;

class FilteringCoursServiceTest extends TestCase
{
    private $coursRepository;
    private $typeCoursRepository;
    private $statusCoursRepository;
    private $filteringCoursService;

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

    public function testFilterCoursWithValidData(): void
    {
        $currentPage = 1;
        $maxPerPage = 10;
        $typeCoursId = 1;
        $dateCoursStr = '2023-10-01';
        $statusCoursId = 1;
        $route = 'api_cours_list';
        $isAdminPath = true;

        $typeCours = new TypeCours();
        $statusCours = new StatusCours();
        $dateCours = new \DateTime($dateCoursStr);

        $this->typeCoursRepository->method('findOneBy')->with(['id' => $typeCoursId])->willReturn($typeCours);
        $this->statusCoursRepository->method('findOneBy')->with(['id' => $statusCoursId])->willReturn($statusCours);

        $coursPaginator = $this->createMock(Paginator::class);
        $coursPaginator->method('count')->willReturn(2);
        $coursPaginator->method('getIterator')->willReturn(new \ArrayIterator([new \stdClass(), new \stdClass()]));
        $this->coursRepository->method('findAllSortByDate')->with($currentPage, $maxPerPage, $typeCours, $dateCours, null, $statusCours)->willReturn($coursPaginator);

        $responseData = $this->filteringCoursService->filterCours($currentPage, $maxPerPage, $typeCoursId, $dateCoursStr, $statusCoursId, $route, $isAdminPath);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        $this->assertCount(2, $responseData['data']);
        $this->assertEquals($currentPage, $responseData['pagination']['currentPage']);
        $this->assertEquals($maxPerPage, $responseData['pagination']['maxPerPage']);
        $this->assertEquals(2, $responseData['pagination']['totalItems']);
        $this->assertEquals(1, $responseData['pagination']['totalPages']);
    }

    public function testFilterCoursWithInvalidDate(): void
    {
        $currentPage = 1;
        $maxPerPage = 10;
        $typeCoursId = 1; // ID valide
        $dateCoursStr = 'invalid-date'; // Date invalide
        $statusCoursId = 1;
        $route = 'api_cours_calendar';
        $isAdminPath = true;

        // Retourner un TypeCours valide pour l'ID fourni
        $this->typeCoursRepository->method('findOneBy')->with(['id' => $typeCoursId])->willReturn(new TypeCours());

        // Attendre l'exception spécifique avec le message et le code appropriés
        $this->expectException(FilteringCoursException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage("La date fournie est invalide");

        // Appeler la méthode qui devrait lever l'exception pour la date invalide
        $this->filteringCoursService->filterCours($currentPage, $maxPerPage, $typeCoursId, $dateCoursStr, $statusCoursId, $route, $isAdminPath);
    }


    public function testFilterCoursWithCalendarRoute(): void
    {
        $currentPage = 1;
        $maxPerPage = 10;
        $typeCoursId = 1;
        $dateCoursStr = '2023-10-01';
        $statusCoursId = 1;
        $route = 'api_cours_calendar';
        $isAdminPath = true;

        $typeCours = new TypeCours(); // Mock TypeCours entity
        $statusCours = new StatusCours(); // Mock StatusCours entity
        $dateCours = new \DateTime($dateCoursStr);
        $dateCours->modify('monday this week');
        $dateLimit = clone $dateCours;
        $dateLimit->modify('+6 days');

        $this->typeCoursRepository->method('findOneBy')->with(['id' => $typeCoursId])->willReturn($typeCours);
        $this->statusCoursRepository->method('findOneBy')->with(['id' => $statusCoursId])->willReturn($statusCours);

        // Create a mock Paginator
        $coursPaginator = $this->createMock(Paginator::class);
        $coursPaginator->method('count')->willReturn(2);
        $coursPaginator->method('getIterator')->willReturn(new \ArrayIterator([new \stdClass(), new \stdClass()]));

        $this->coursRepository->method('findAllSortByDate')->with($currentPage, $maxPerPage, $typeCours, $dateCours, $dateLimit, $statusCours)->willReturn($coursPaginator);

        $responseData = $this->filteringCoursService->filterCours($currentPage, $maxPerPage, $typeCoursId, $dateCoursStr, $statusCoursId, $route, $isAdminPath);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('pagination', $responseData);
        $this->assertCount(2, $responseData['data']);
        $this->assertEquals($currentPage, $responseData['pagination']['currentPage']);
        $this->assertEquals($maxPerPage, $responseData['pagination']['maxPerPage']);
        $this->assertEquals(2, $responseData['pagination']['totalItems']);
        $this->assertEquals(1, $responseData['pagination']['totalPages']);
    }

    public function testFilterCoursWithTypeCoursNotFound(): void
    {
        $currentPage = 1;
        $maxPerPage = 10;
        $typeCoursId = 999; // ID inexistant
        $dateCoursStr = '2023-10-01';
        $statusCoursId = 1;
        $route = 'api_cours_calendar';
        $isAdminPath = true;

        // Configurer le mock pour retourner null lorsque l'ID n'existe pas
        $this->typeCoursRepository->method('findOneBy')->with(['id' => $typeCoursId])->willReturn(null);

        // Attendre l'exception spécifique avec le message et le code appropriés
        $this->expectException(FilteringCoursException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage("Le type de cours fourni est invalide");

        // Appeler la méthode qui devrait lever l'exception
        $this->filteringCoursService->filterCours($currentPage, $maxPerPage, $typeCoursId, $dateCoursStr, $statusCoursId, $route, $isAdminPath);
    }


}
