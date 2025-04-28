<?php

namespace App\Tests\Service\Cours;

use App\Entity\Cours;
use App\Service\CoursControllerService\AddUserService\AddUserTimeCheckerService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AddUserTimeCheckerServiceTest extends TestCase
{
    private AddUserTimeCheckerService $addUserTimeCheckerService;

    protected function setUp(): void
    {
        $this->addUserTimeCheckerService = new AddUserTimeCheckerService();
    }

    public static function lateRegistrationDataProvider(): iterable
    // Cas où la date du cours est dans le passé ou très proche du présent (moins de 30 minutes)
    {
        yield 'cours_in_the past' => [new \DateTimeImmutable('-1 hour')];
        yield 'cours_in_29_minutes' => [new \DateTimeImmutable('+29 minutes')];
        yield 'cours_now' => [new \DateTimeImmutable('now')];
    }

    public static function notLateRegistrationDataProvider(): iterable
    // Cas où la date du cours est dans le futur (plus de 30 minutes)
    {
        yield 'cours_in_31_minutes' => [new \DateTimeImmutable('+31 minutes')];
        yield 'cours_tomorrow' => [new \DateTimeImmutable('+1 day')];
        yield 'cours_in_two_hours' => [new \DateTimeImmutable('+2 hours')];
    }

    #[DataProvider('lateRegistrationDataProvider')]
    public function testIsTooLateRegisterThrowsException(\DateTimeImmutable $date): void
    {
        $cours = new Cours();
        $cours->setDateCours($date);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Inscription impossible : les inscriptions doivent être effectuées au moins 30 minutes avant le début du cours.');
        $this->expectExceptionCode(403);

        $this->addUserTimeCheckerService->isTooLateRegister($cours);
    }

    #[DataProvider('notLateRegistrationDataProvider')]
    public function testIsTooLateRegisterDoesNotThrowException(\DateTimeImmutable $date): void
    {
        $cours = new Cours();
        $cours->setDateCours($date);

        $this->addUserTimeCheckerService->isTooLateRegister($cours);
        $this->assertTrue(true, 'L\'exception ne devrait pas être lancée.'); // Assertion pour indiquer le succès
    }
}
