<?php

namespace App\Tests\Service\User;

use App\Entity\User;
use App\Service\CoursControllerService\AddUserService\HandleUserCreditService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class HandleUserCreditServiceTest extends TestCase
{
    private HandleUserCreditService $handleUserCreditService;
    private Security $security;
    private User $user;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->user = $this->createMock(User::class);
        $this->handleUserCreditService = new HandleUserCreditService($this->security);
    }

    public static function dataProviderDecrement(): iterable
    {
        yield 'not_on_waiting_list' => [
            'isOnWaitingList' => false,
            'initialNombreCours' => 1,
            'expectedNombreCours' => 0,
            'shouldDecrement' => true,
        ];

        yield 'on_waiting_list' => [
            'isOnWaitingList' => true,
            'initialNombreCours' => 1,
            'expectedNombreCours' => 1,
            'shouldDecrement' => false,
        ];

        yield 'not_on_waiting_list_multiple_cours' => [
            'isOnWaitingList' => false,
            'initialNombreCours' => 3,
            'expectedNombreCours' => 2,
            'shouldDecrement' => true,
        ];

        yield 'on_waiting_list_zero_cours' => [
            'isOnWaitingList' => true,
            'initialNombreCours' => 0,
            'expectedNombreCours' => 0,
            'shouldDecrement' => false,
        ];
    }

    #[DataProvider('dataProviderDecrement')]
    public function testDecrement(
        bool $isOnWaitingList,
        int $initialNombreCours,
        int $expectedNombreCours,
        bool $shouldDecrement,
    ): void {
        // Configuration du mock de l'utilisateur
        $this->security->expects($isOnWaitingList ? $this->never() : $this->once())
            ->method('getUser')
            ->willReturn($this->user);
        $this->user->expects($shouldDecrement ? $this->once() : $this->never())
            ->method('getNombreCours')
            ->willReturn($initialNombreCours);

        $this->user->expects($shouldDecrement ? $this->once() : $this->never())
            ->method('setNombreCours')
            ->with($this->equalTo($expectedNombreCours));

        $this->handleUserCreditService->decrement($isOnWaitingList);
    }
}
