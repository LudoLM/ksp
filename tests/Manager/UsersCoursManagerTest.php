<?php

namespace App\Tests\Manager;

use App\Entity\Cours;
use App\Entity\User;
use App\Entity\UsersCours;
use App\Manager\UsersCoursManager;
use App\Service\CoursControllerService\CountUsersInCoursService;
use App\Service\NotificationService\NotificationsUsersActionsService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UsersCoursManagerTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject&NotificationsUsersActionsService $notificationsUsersActionsService;
    public \PHPUnit\Framework\MockObject\MockObject $countUsersInCoursService;
    public $usersCoursManager;

    public function setUp(): void
    {
        $this->countUsersInCoursService = $this->createMock(CountUsersInCoursService::class);
        $this->notificationsUsersActionsService = $this->createMock(NotificationsUsersActionsService::class);
        $this->usersCoursManager = new UsersCoursManager($this->countUsersInCoursService, $this->notificationsUsersActionsService);
    }

    public static function dataProvider(): \Generator
    {
        yield 'RemoveFromCoursAndIncrementNombreCours' => [
            'participantIds' => [1, 2],
            'isOnWaitingList' => false,
            'expectIncrement' => true,
            'expectRemoval' => true,
        ];

        yield 'RemoveFromWaitingListWithoutIncrement' => [
            'participantIds' => [1, 2],
            'isOnWaitingList' => true,
            'expectIncrement' => false,
            'expectRemoval' => true,
        ];

        yield 'NoParticipantsToRemove' => [
            'participantIds' => [],
            'isOnWaitingList' => false,
            'expectIncrement' => false,
            'expectRemoval' => false,
        ];

        yield 'ParticipantNotInCours' => [
            'participantIds' => [999],
            'isOnWaitingList' => false,
            'expectIncrement' => false,
            'expectRemoval' => false,
        ];
    }

    #[DataProvider('dataProvider')]
    public function testProcessRemovalFromCours(
        array $participantIds,
        bool $isOnWaitingList,
        bool $expectIncrement,
        bool $expectRemoval,
    ): void {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getNombreCours')->willReturn(5);

        $usersCours = $this->createMock(UsersCours::class);
        $usersCours->method('getUser')->willReturn($user);

        $cours = $this->createMock(Cours::class);
        $cours->method('getUsersCours')->willReturn(new ArrayCollection([$usersCours]));

        if ($expectRemoval) {
            $usersCours->expects($this->once())
                ->method('setUnsubscribedAt')
                ->with($this->isInstanceOf(\DateTimeImmutable::class));
        } else {
            $usersCours->expects($this->never())->method('setUnsubscribedAt');
        }

        if ($expectIncrement) {
            $user->expects($this->once())
                ->method('setNombreCours')
                ->with(6);
        } else {
            $user->expects($this->never())->method('setNombreCours');
        }

        $this->countUsersInCoursService
            ->expects($this->once())
            ->method('reopenIfCapacityAvailable')
            ->with($cours);

        $this->usersCoursManager->processRemovalFromCours(
            $cours,
            $participantIds,
            $isOnWaitingList
        );
    }
}
