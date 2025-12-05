<?php

namespace App\Tests\Service;

use App\Entity\Cours;
use App\Entity\HistoriquePaiement;
use App\Entity\Pack;
use App\Entity\TypeCours;
use App\Entity\User;
use App\Entity\UsersCours;
use App\Service\NotificationService\NotificationsUsersActionsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Serializer\SerializerInterface;

class NotificationsUsersActionsServiceTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject&HubInterface $hub;
    private \PHPUnit\Framework\MockObject\MockObject&SerializerInterface $serializer;
    public $notificationsUsersActionsService;

    public function setUp(): void
    {
        $this->hub = $this->createMock(HubInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->notificationsUsersActionsService = new NotificationsUsersActionsService($this->hub, $this->serializer);
    }

    public static function dataProvider(): \Generator
    {
        $uc1 = [ // Sera une Inscription le 2022-01-05
            'createdAt' => '2022-01-05 10:00:00',
            'unsubscribedAt' => null,
            'firstname' => 'Bob',
            'name' => 'Dylan',
            'typeCours' => 'Yoga',
            'dateCours' => '2022-04-01 00:00:00',
        ];
        $hp1 = [ // Sera un Paiement le 2022-01-07
            'datePayment' => '2022-01-07 15:00:00',
            'firstname' => 'Robert',
            'name' => 'De Niro',
            'packName' => 'Pack Zen',
        ];
        $uc2 = [ // Sera une Désinscription le 2022-01-01
            'createdAt' => '2022-01-01 09:00:00',
            'unsubscribedAt' => '2022-01-01 12:00:00',
            'firstname' => 'Bobby',
            'name' => 'Fischer',
            'typeCours' => 'Pilates',
            'dateCours' => '2022-04-01 00:00:00',
        ];

        yield 'activities_sorted_desc' => [
            'usersCoursData' => [$uc1, $uc2],
            'paiementsData' => [$hp1],
            'expectedActivities' => [
                [
                    'type' => 'Achat de cours',
                    'dateAction' => '2022-01-07 15:00:00',
                    'userName' => 'Robert De Niro',
                    'subject' => 'Pack Zen',
                    'dateSubject' => '2022-01-07 15:00:00',
                ],
                [
                    'type' => 'Inscription',
                    'dateAction' => '2022-01-05 10:00:00',
                    'userName' => 'Bob Dylan',
                    'subject' => 'Yoga',
                    'dateSubject' => '2022-04-01 00:00:00',
                ],
                [
                    'type' => 'Désinscription',
                    'dateAction' => '2022-01-01 12:00:00',
                    'userName' => 'Bobby Fischer',
                    'subject' => 'Pilates',
                    'dateSubject' => '2022-04-01 00:00:00',
                ],
            ],
        ];
    }

    private function createMockUser(string $firstName, string $lastName): MockObject
    {
        $userMock = $this->createMock(User::class);
        $userMock->method('getPrenom')->willReturn($firstName);
        $userMock->method('getNom')->willReturn($lastName);

        return $userMock;
    }

    private function createMockUsersCours(
        string $createdAt,
        ?string $unsubscribedAt,
        string $firstName,
        string $lastName,
        string $typeCoursLibelle,
        string $dateCours,
    ): MockObject {
        // Mocks User, Cours, TypeCours
        $user = $this->createMockUser($firstName, $lastName);
        $typeCoursMock = $this->createMock(TypeCours::class);
        $typeCoursMock->method('getLibelle')->willReturn($typeCoursLibelle);

        $coursMock = $this->createMock(Cours::class);
        $coursMock->method('getTypeCours')->willReturn($typeCoursMock);
        $coursMock->method('getDateCours')->willReturn(new \DateTimeImmutable($dateCours));

        $usersCoursMock = $this->createMock(UsersCours::class);
        $usersCoursMock->method('getUser')->willReturn($user);
        $usersCoursMock->method('getCours')->willReturn($coursMock);
        $usersCoursMock->method('getCreatedAt')->willReturn(new \DateTimeImmutable($createdAt));
        $usersCoursMock->method('getUnsubscribedAt')->willReturn(
            $unsubscribedAt ? new \DateTimeImmutable($unsubscribedAt) : null
        );
        $usersCoursMock->method('isOnWaitingList')->willReturn(false);

        return $usersCoursMock;
    }

    private function createMockHistoriquePaiement(
        string $datePayment,
        string $firstName,
        string $lastName,
        string $packName,
    ): MockObject {
        // Mocks User et Pack
        $user = $this->createMockUser($firstName, $lastName);
        $packMock = $this->createMock(Pack::class);
        $packMock->method('getNom')->willReturn($packName);

        $historiquePaiementMock = $this->createMock(HistoriquePaiement::class);
        $historiquePaiementMock->method('getUser')->willReturn($user);
        $historiquePaiementMock->method('getPack')->willReturn($packMock);
        $historiquePaiementMock->method('getDate')->willReturn(new \DateTimeImmutable($datePayment));

        return $historiquePaiementMock;
    }

    #[DataProvider('dataProvider')]
    public function testToNotificationShouldMergeAndSortActivitiesCorrectly(
        array $usersCoursData,
        array $paiementsData,
        array $expectedActivities,
    ): void {
        // Mocks
        $usersCoursMocks = array_map(fn (array $data): MockObject => $this->createMockUsersCours(
            $data['createdAt'], $data['unsubscribedAt'], $data['firstname'],
            $data['name'], $data['typeCours'], $data['dateCours']
        ), $usersCoursData);

        $paiementsMocks = array_map(fn (array $data): MockObject => $this->createMockHistoriquePaiement(
            $data['datePayment'], $data['firstname'], $data['name'], $data['packName']
        ), $paiementsData);

        // Execution
        $activities = $this->notificationsUsersActionsService->toNotification($usersCoursMocks, $paiementsMocks);

        // Assertions
        $this->assertCount(count($usersCoursData) + count($paiementsData), $activities);

        foreach ($expectedActivities as $index => $expected) {
            $actual = $activities[$index];
            $this->assertSame($expected['type'], $actual->type, "L'activité à l'index $index n'a pas le type attendu.");
            $this->assertEquals(new \DateTimeImmutable($expected['dateAction']), $actual->dateAction, "L'activité à l'index $index n'a pas la date d'action attendue.");
            $this->assertSame($expected['userName'], $actual->userName, "L'activité à l'index $index n'a pas le nom d'utilisateur attendu.");
            $this->assertSame($expected['subject'], $actual->subject, "L'activité à l'index $index n'a pas le sujet attendu.");
            $this->assertEquals(new \DateTimeImmutable($expected['dateSubject']), $actual->dateSubject, "L'activité à l'index $index n'a pas la date de sujet attendue.");
        }
    }
}
