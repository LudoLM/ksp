<?php

namespace App\Tests\Service;

use App\Entity\Cours;
use App\Entity\User;
use App\Entity\UsersCours;
use App\Service\CoursControllerService\ActionsModifyOpenedCoursService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ActionsModifyOpenedCoursServiceTest extends TestCase
{
    private MessageBusInterface&MockObject $messageBus;
    private Security&MockObject $security;
    private ActionsModifyOpenedCoursService $actionsModifyOpenedCoursService;

    protected function setUp(): void
    {
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->actionsModifyOpenedCoursService = new ActionsModifyOpenedCoursService($this->messageBus, $this->security);
    }

    public static function dataProvider(): \Generator
    {
        yield 'date_not_changed_duration_not_changed' => [
            'initialDate' => new \DateTimeImmutable('2023-10-31T00:00:00.000000+0000'),
            'initialDuration' => 60,
            'expectedDispatchCount' => 0, // Aucun message attendu
        ];

        yield 'date_changed_duration_not_changed' => [
            'initialDate' => new \DateTimeImmutable('2023-10-30T00:00:00.000000+0000'),
            'initialDuration' => 60,
            'expectedDispatchCount' => 2, // 1 pour UpdateStatus + 2 pour les emails
        ];

        yield 'date_not_changed_duration_changed' => [
            'initialDate' => new \DateTimeImmutable('2023-10-31T00:00:00.000000+0000'),
            'initialDuration' => 50,
            'expectedDispatchCount' => 2, // 1 pour UpdateStatus + 2 pour les emails
        ];

        yield 'date_changed_duration_changed' => [
            'initialDate' => new \DateTimeImmutable('2023-10-30T00:00:00.000000+0000'),
            'initialDuration' => 50,
            'expectedDispatchCount' => 2, // 1 pour UpdateStatus + 2 pour les emails
        ];
    }

    #[DataProvider('dataProvider')]
    public function testHandleDispatchesCorrectMessages(\DateTimeInterface $initialDate, int $initialDuration, int $expectedDispatchCount): void
    {
        // Configurer le mock pour MessageBus
        $this->messageBus
            ->expects($this->exactly($expectedDispatchCount))
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass()));

        // Préparer un cours
        $cours = new Cours();
        $cours->setId(100);
        $cours->setDateCours(new \DateTimeImmutable('2023-10-31T00:00:00.000000+0000'));
        $cours->setDuree(60);

        // Simuler un utilisateur connecté
        $user = new User();
        $reflectionClass = new \ReflectionClass($user);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($user, 100);

        $this->security
            ->method('getUser')
            ->willReturn($user);

        $userCours = new UsersCours();
        $userCours->setUser($user);
        $reflectionClass = new \ReflectionClass($userCours);
        $reflectionProperty = $reflectionClass->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($userCours, 100);
        $cours->addUsersCours($userCours);

        // Appeler la méthode handle avec les paramètres de test
        $this->actionsModifyOpenedCoursService->handle($cours, $initialDuration, $initialDate);
    }
}
