<?php

namespace App\Tests\Service\StripeService;

use App\Entity\HistoriquePaiement;
use App\Entity\User;
use App\Repository\HistoriquePaiementRepository;
use App\Repository\PackRepository;
use App\Repository\UserRepository;
use App\Service\StripeService\StripeCheckoutCreditService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Stripe\Collection;
use Stripe\Service\Checkout\CheckoutServiceFactory;
use Stripe\Service\Checkout\SessionService;
use Stripe\StripeClient;

class StripeCheckoutCreditServiceTest extends TestCase
{
    private MockObject $stripeClientMock;
    private MockObject&EntityManager $entityManagerMock;
    private MockObject $packRepositoryMock;
    private MockObject $historiquePaiementRepositoryMock;
    private MockObject $userRepositoryMock;
    private StripeCheckoutCreditService $service;

    protected function setUp(): void
    {
        $this->stripeClientMock = $this->createMock(StripeClient::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->packRepositoryMock = $this->createMock(PackRepository::class);
        $this->historiquePaiementRepositoryMock = $this->createMock(HistoriquePaiementRepository::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);

        $this->entityManagerMock->method('wrapInTransaction')
            ->willReturnCallback(fn (callable $func) => $func());

        $this->service = new StripeCheckoutCreditService(
            $this->stripeClientMock,
            $this->entityManagerMock,
            $this->packRepositoryMock,
            $this->historiquePaiementRepositoryMock,
            $this->userRepositoryMock,
            $this->createMock(LoggerInterface::class),
        );
    }

    private function createUser(int $id, int $nombreCours = 0): User
    {
        $user = new User();
        $user->setNombreCours($nombreCours);

        $idProp = new \ReflectionClass($user)->getProperty('id');
        $idProp->setValue($user, $id);

        return $user;
    }

    private function mockStripeSession(
        string $paymentStatus,
        int $nombreCours,
        string $packName,
        int $unitAmount,
        ?string $clientReferenceId,
    ): void {
        $sessionMock = $this->createMock(Session::class);
        $sessionMock->method('__get')
            ->willReturnCallback(fn (string $property): ?string => match ($property) {
                'payment_status' => $paymentStatus,
                'client_reference_id' => $clientReferenceId,
                default => null,
            });

        $lineItemsPayload = [
            'data' => [[
                'price' => [
                    'unit_amount' => $unitAmount,
                    'product' => [
                        'name' => $packName,
                        'metadata' => ['nombreCours' => $nombreCours],
                    ],
                ],
            ]],
        ];
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->method('toJSON')->willReturn(json_encode($lineItemsPayload, JSON_THROW_ON_ERROR));

        $sessionServiceMock = $this->createMock(SessionService::class);
        $sessionServiceMock->method('retrieve')->willReturn($sessionMock);
        $sessionServiceMock->method('allLineItems')->willReturn($collectionMock);

        $checkoutFactoryMock = $this->createMock(CheckoutServiceFactory::class);
        $checkoutFactoryMock->method('__get')->with('sessions')->willReturn($sessionServiceMock);

        $this->stripeClientMock->method('__get')->with('checkout')->willReturn($checkoutFactoryMock);
    }

    public function testAlreadyProcessedWhenCheckoutIdAlreadyKnown(): void
    {
        $user = $this->createUser(1);

        $this->historiquePaiementRepositoryMock->method('findBy')->willReturn([new HistoriquePaiement()]);

        $this->stripeClientMock->expects($this->never())->method('__get');
        $this->entityManagerMock->expects($this->never())->method('flush');

        $result = $this->service->creditUserForCheckoutSession($user, 'cs_test_already');

        $this->assertSame('already_processed', $result['status']);
    }

    public function testNotPaidSessionIsNotCredited(): void
    {
        $user = $this->createUser(1, 0);

        $this->historiquePaiementRepositoryMock->method('findBy')->willReturn([]);
        $this->mockStripeSession('unpaid', 5, 'Carte de 5 séances', 8000, clientReferenceId: '1');

        $this->entityManagerMock->expects($this->never())->method('flush');

        $result = $this->service->creditUserForCheckoutSession($user, 'cs_test_unpaid');

        $this->assertSame('not_paid', $result['status']);
        $this->assertSame(0, $user->getNombreCours());
    }

    public function testForbiddenWhenSessionBelongsToAnotherUser(): void
    {
        // La session cs_test_A a été payée par l'utilisateur 1 (client_reference_id = '1'),
        // mais c'est l'utilisateur 2 qui appelle creditUserForCheckoutSession avec cet id
        // (checkoutSessionId deviné/récupéré/fuité) : doit être refusé, sans aucun crédit.
        $userB = $this->createUser(2, 0);

        $this->historiquePaiementRepositoryMock->method('findBy')->willReturn([]);
        $this->mockStripeSession('paid', 5, 'Carte de 5 séances', 8000, clientReferenceId: '1');

        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->userRepositoryMock->expects($this->never())->method('addCredits');

        $result = $this->service->creditUserForCheckoutSession($userB, 'cs_test_A');

        $this->assertSame('forbidden', $result['status']);
        $this->assertSame(0, $userB->getNombreCours());
    }

    public function testCreditsUserWhenClientReferenceIdMatches(): void
    {
        // La session cs_test_A appartient bien à l'utilisateur qui l'utilise : doit être créditée.
        $userA = $this->createUser(1, 2);

        $this->historiquePaiementRepositoryMock->method('findBy')->willReturn([]);
        $this->packRepositoryMock->method('findBy')->willReturn([]);
        $this->mockStripeSession('paid', 5, 'Carte de 5 séances', 8000, clientReferenceId: '1');

        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->entityManagerMock->expects($this->exactly(2))->method('persist');
        $this->userRepositoryMock->expects($this->once())->method('addCredits')->with(1, 5);

        // refresh() simule le rechargement depuis la base après l'incrément atomique en DQL.
        $this->entityManagerMock->method('refresh')
            ->willReturnCallback(function (User $user): void {
                $user->setNombreCours($user->getNombreCours() + 5);
            });

        $result = $this->service->creditUserForCheckoutSession($userA, 'cs_test_A');

        $this->assertSame('credited', $result['status']);
        $this->assertSame(7, $result['userQuantity']);
    }
}
