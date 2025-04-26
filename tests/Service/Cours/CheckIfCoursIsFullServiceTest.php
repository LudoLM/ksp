<?php

namespace App\Tests\Service\Cours;

use App\Entity\Cours;
use App\Repository\StatusCoursRepository;
use App\Service\CoursControllerService\AddUserService\CheckIfCoursIsFullService;
use App\Service\CoursControllerService\CountUsersInCoursService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CheckIfCoursIsFullServiceTest extends TestCase
{
    private CheckIfCoursIsFullService $checkIfCoursIsFullService;

    protected function setUp(): void
    {
        $countUsersInCoursService = $this->createMock(CountUsersInCoursService::class);
        $statusCoursRepository = $this->createMock(StatusCoursRepository::class);
        $this->checkIfCoursIsFullService = new CheckIfCoursIsFullService($countUsersInCoursService, $statusCoursRepository);
    }

    public static function dataProvider(): \Generator
    {
        yield 'cours_full_not_on_waiting_list' => [
            'nbInscritionsMax' => 2,
            'usersCount' => 2,
            'isOnWaitingList' => false,
            'exceptionMessage' => 'Le cours est complet',
        ];

        yield 'cours_not_full_not_on_waiting_list' => [
            'nbInscritionsMax' => 2,
            'usersCount' => 1,
            'isOnWaitingList' => false,
            'exceptionMessage' => null,
        ];

        yield 'cours_full_on_waiting_list' => [
            'nbInscritionsMax' => 2,
            'usersCount' => 2,
            'isOnWaitingList' => true,
            'exceptionMessage' => null,
        ];

        yield 'cours_over_full_not_on_waiting_list' => [
            'nbInscritionsMax' => 2,
            'usersCount' => 3,
            'isOnWaitingList' => false,
            'exceptionMessage' => 'Le cours est complet',
        ];

        yield 'cours_over_full_on_waiting_list' => [
            'nbInscritionsMax' => 2,
            'usersCount' => 3,
            'isOnWaitingList' => true,
            'exceptionMessage' => null,
        ];
    }

    #[DataProvider('dataProvider')]
    public function testCheckIfCoursIsFull(
        int $nbInscritionsMax,
        int $usersCount,
        bool $isOnWaitingList,
        ?string $exceptionMessage,
    ): void {
        $cours = new Cours();
        $cours->setNbInscriptionMax($nbInscritionsMax);

        if (null !== $exceptionMessage) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage($exceptionMessage);
            $this->checkIfCoursIsFullService->checkIfCoursIsFull($cours, $usersCount, $isOnWaitingList);
        } else {
            $this->checkIfCoursIsFullService->checkIfCoursIsFull($cours, $usersCount, $isOnWaitingList);
            $this->assertTrue(true, 'L\'exception ne devrait pas être lancée.');
        }
    }
}
