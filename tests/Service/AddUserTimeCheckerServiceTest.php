<?php

namespace App\Tests\Service;

use App\Entity\Cours;
use App\Service\CoursControllerService\AddUserTimeCheckerService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AddUserTimeCheckerServiceTest extends TestCase
{
    private AddUserTimeCheckerService $addUserTimeCheckerService;

    protected function setUp(): void
    {
        $this->addUserTimeCheckerService = new AddUserTimeCheckerService();
    }

    public static function dataProvider(): \Generator
    {
        yield 'past_date_should_return_false' => [
            'date' => (new \DateTimeImmutable())->sub(new \DateInterval('P1D')),
            'expected' => true,
        ];

        yield 'future_date_should_return_true' => [
            'date' => (new \DateTimeImmutable())->add(new \DateInterval('P1D')),
            'expected' => false,
        ];
    }

    #[DataProvider('dataProvider')]
    public function testIsTooLateRegister(\DateTimeImmutable $date, bool $expected): void
    {
        $cours = new Cours();
        $cours->setDateCours($date);

        $result = $this->addUserTimeCheckerService->isTooLateRegister($cours);

        $this->assertSame($expected, $result);
    }
}
