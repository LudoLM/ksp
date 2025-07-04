<?php

namespace App\Tests\Service\Cours;

use App\Entity\Cours;
use App\Entity\User;
use App\Entity\UsersCours;
use App\Service\CoursControllerService\AddUserService\handleCheckSubscriptionsInAWeek;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HandleCheckSubscriptionsInAWeekTest extends TestCase
{
    public $handleCheckSubscriptionsInAWeek;

    protected function setUp(): void
    {
        $this->handleCheckSubscriptionsInAWeek = new handleCheckSubscriptionsInAWeek();
    }

    // Data provider for test cases
    public static function userProvider(): \Generator
    {
        yield 'user_with_two_subscriptions_in_same_week' => [
            'date' => new \DateTime('2023-10-30T00:00:00.000000+0000'),
            'hasLimit' => true,
            'date1' => new \DateTime('2023-10-31T00:00:00.000000+0000'),
            'hasLimit1' => true,
            'isOnWaitingList1' => false,
            'date2' => new \DateTime('2023-11-01T00:00:00.000000+0000'),
            'hasLimit2' => true,
            'isOnWaitingList2' => false,
            'expectedStatus' => 403,
            'expectedMessage' => 'Vous avez déjà deux reservations de cours pour cette semaine.',
        ];

        yield 'user_with_no_subscriptions_in_same_week' => [
            'date' => new \DateTime('2023-10-30T00:00:00.000000+0000'),
            'hasLimit' => true,
            'date1' => new \DateTime('2023-12-31T00:00:00.000000+0000'),
            'hasLimit1' => true,
            'isOnWaitingList1' => false,
            'date2' => new \DateTime('2023-12-08T00:00:00.000000+0000'),
            'hasLimit2' => true,
            'isOnWaitingList2' => false,
            'expectedStatus' => null,
            'expectedMessage' => null,
        ];

        yield 'user_with_three_subscriptions_in_same_weekButNoLimited' => [
            'date' => new \DateTime('2023-10-30T00:00:00.000000+0000'),
            'hasLimit' => true,
            'date1' => new \DateTime('2023-10-31T00:00:00.000000+0000'),
            'hasLimit1' => true,
            'isOnWaitingList1' => false,
            'date2' => new \DateTime('2023-11-01T00:00:00.000000+0000'),
            'hasLimit2' => false,
            'isOnWaitingList2' => false,
            'expectedStatus' => null,
            'expectedMessage' => null,
        ];

        yield 'user_with_three_subscriptions_in_same_weekButOneIsOnWaitingList' => [
            'date' => new \DateTime('2023-10-30T00:00:00.000000+0000'),
            'hasLimit' => true,
            'date1' => new \DateTime('2023-10-31T00:00:00.000000+0000'),
            'hasLimit1' => true,
            'isOnWaitingList1' => false,
            'date2' => new \DateTime('2023-11-01T00:00:00.000000+0000'),
            'hasLimit2' => true,
            'isOnWaitingList2' => true,
            'expectedStatus' => null,
            'expectedMessage' => null,
        ];
    }

    #[DataProvider('userProvider')]
    public function testCheckIfUserAlreadyHasTwoSubscriptionsInTheSameWeek(
        \DateTimeInterface $date,
        bool $hasLimit,
        \DateTimeInterface $date1,
        bool $hasLimit1,
        bool $isOnWaitingList1,
        \DateTimeInterface $date2,
        bool $hasLimit2,
        bool $isOnWaitingList2,
        ?int $expectedStatus,
        ?string $expectedMessage,
    ): void {
        $user = new User();
        $cours = new Cours();
        $userCours1 = new UsersCours();
        $userCours2 = new UsersCours();

        $cours->setDateCours($date);
        $cours->setHasLimitOfOneCoursPerWeek($hasLimit);

        $cours1 = new Cours();
        $cours1->setDateCours($date1);
        $cours1->setHasLimitOfOneCoursPerWeek($hasLimit1);
        $cours2 = new Cours();
        $cours2->setDateCours($date2);
        $cours2->setHasLimitOfOneCoursPerWeek($hasLimit2);
        $userCours1->setCours($cours1);
        $userCours1->setIsOnWaitingList($isOnWaitingList1);
        $userCours2->setCours($cours2);
        $userCours2->setIsOnWaitingList($isOnWaitingList2);
        $user->addUsersCour($userCours1);
        $user->addUsersCour($userCours2);

        if (null !== $expectedStatus) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage($expectedMessage);
            $this->handleCheckSubscriptionsInAWeek->checkIfUserAlreadyHasTwoSubscriptionsInTheSameWeek($user, $cours);
        } else {
            $this->handleCheckSubscriptionsInAWeek->checkIfUserAlreadyHasTwoSubscriptionsInTheSameWeek($user, $cours);
            $this->assertTrue(true, 'L\'exception ne devrait pas être lancée.');
        }
    }
}
