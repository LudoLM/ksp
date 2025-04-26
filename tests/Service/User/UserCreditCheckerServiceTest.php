<?php

namespace App\Tests\Service\User;

use App\Entity\User;
use App\Service\CoursControllerService\AddUserService\UserCreditCheckerService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserCreditCheckerServiceTest extends TestCase
{
    private UserCreditCheckerService $userCreditCheckerService;

    protected function setUp(): void
    {
        // Utilisation de la classe réelle
        $this->userCreditCheckerService = new UserCreditCheckerService();
    }

    public static function dataProviderCheckAccountCredits(): iterable
    {
        yield 'has_credits_admin' => [
            'quantityCours' => 3,
            'isAdmin' => true,
            'exceptionMessage' => null,
        ];
        yield 'has_credits_not_admin' => [
            'quantityCours' => 3,
            'isAdmin' => false,
            'exceptionMessage' => null,
        ];
        yield 'no_credits_admin' => [
            'quantityCours' => 0,
            'isAdmin' => true,
            'exceptionMessage' => "Bob n'a pas assez de crédits pour s'inscrire à ce cours.",
        ];
        yield 'no_credits_not_admin' => [
            'quantityCours' => 0,
            'isAdmin' => false,
            'exceptionMessage' => "Vous n'avez pas assez de crédits pour vous inscrire à ce cours.",
        ];
    }

    #[DataProvider('dataProviderCheckAccountCredits')]
    public function testCheckAccountCredits(
        int $quantityCours,
        bool $isAdmin,
        ?string $exceptionMessage,
    ): void {
        $user = new User();
        $user->setPrenom('Bob');
        $user->setNombreCours($quantityCours);

        if (null !== $exceptionMessage) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage($exceptionMessage);
        }

        $this->userCreditCheckerService->checkAccountCredits(
            $user,
            $isAdmin,
        );

        // Si aucune exception n'est attendue, ce point sera atteint.
        $this->assertNull($exceptionMessage, 'Une exception inattendue a été levée.');
    }
}
