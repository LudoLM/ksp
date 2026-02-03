<?php

namespace App\Tests\Service\Stripe;

use App\Service\StripeService\BalanceStripeService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stripe\BalanceTransaction;
use Stripe\Collection;
use Stripe\Service\BalanceTransactionService;
use Stripe\StripeClient;
use Symfony\Contracts\Cache\CacheInterface;

class BalanceStripeServiceTest extends TestCase
{
    private BalanceStripeService $balanceStripeService;
    private MockObject $stripeClientMock;
    private MockObject $cacheMock;
    private int $testMonth = 11;
    private int $testYear = 2025;

    private int $expectedStartTimestamp;
    private int $expectedEndTimestamp;

    protected function setUp(): void
    {
        $this->stripeClientMock = $this->createMock(StripeClient::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);

        $this->cacheMock->method('get')
            ->willReturnCallback(fn ($key, $callback) => $callback());

        $this->balanceStripeService = new BalanceStripeService(
            $this->stripeClientMock,
            $this->cacheMock
        );

        $this->expectedStartTimestamp = new \DateTimeImmutable('2025-11-01 00:00:00')->getTimestamp();
        $this->expectedEndTimestamp = new \DateTimeImmutable('2025-12-01 00:00:00')->getTimestamp();
    }

    // --- DATA PROVIDER ---

    public static function calculationScenariosProvider(): \Generator
    {
        yield 'ArrondiSimple_TauxStandard' => [
            'transactionData' => [
                ['amount' => 12000, 'fee' => 500, 'net' => 11500],
                ['amount' => 24000, 'fee' => 1000, 'net' => 23000],
            ],
            'expectedResults' => [
                'gross' => '360.00',
                'fees' => '15.00',
                'ht' => '300.00',
                'tva' => '60.00',
                'netStripe' => '345.00',
            ],
        ];

        yield 'ArrondiComplexe_CalculHT' => [
            'transactionData' => [
                ['amount' => 12123, 'fee' => 150, 'net' => 11973],
            ],
            'expectedResults' => [
                'gross' => '121.23',
                'fees' => '1.50',
                'ht' => '101.03',
                'tva' => '20.20',
                'netStripe' => '119.73',
            ],
        ];
    }

    #[DataProvider('calculationScenariosProvider')]
    public function testGetBalance(array $transactionData, array $expectedResults): void
    {
        // Mocks

        $mockTransactions = [];
        foreach ($transactionData as $data) {
            $mockTransaction = $this->createMock(BalanceTransaction::class);
            $mockTransaction->method('__get')
                ->willReturnCallback(fn ($property) => match ($property) {
                    'amount' => $data['amount'],
                    'fee' => $data['fee'],
                    'net' => $data['net'],
                    default => null,
                });

            $mockTransactions[] = $mockTransaction;
        }
        $collectionMock = $this->createMock(Collection::class);

        $collectionMock->expects($this->once())
            ->method('autoPagingIterator')
            ->willReturn(new \ArrayIterator($mockTransactions));

        $balanceTransactionServiceMock = $this->createMock(BalanceTransactionService::class);

        $balanceTransactionServiceMock->expects($this->once())
            ->method('all')
            ->with([
                'created' => [
                    'gte' => $this->expectedStartTimestamp,
                    'lt' => $this->expectedEndTimestamp,
                ],
                'type' => 'charge',
                'limit' => 100,
            ])
            ->willReturn($collectionMock);

        $this->stripeClientMock->method('__get')
            ->with('balanceTransactions')
            ->willReturn($balanceTransactionServiceMock);

        // Appel de la méthode

        $result = $this->balanceStripeService->getBalance($this->testMonth, $this->testYear);

        // Asserts

        $this->assertIsArray($result);
        $this->assertEquals($expectedResults['gross'], $result['gross'], 'Gross (Brut TTC) calculation error.');
        $this->assertEquals($expectedResults['fees'], $result['fees'], 'Fees (Frais) calculation error.');
        $this->assertEquals($expectedResults['ht'], $result['ht'], 'HT (Hors Taxe) calculation error.');
        $this->assertEquals($expectedResults['tva'], $result['tva'], 'TVA calculation error.');
        $this->assertEquals($expectedResults['netStripe'], $result['netStripe'], 'NetStripe calculation error.');
        $this->assertIsString($result['gross']);
    }

    public function testGetBalanceBypassesCacheForCurrentMonth(): void
    {
        $currentMonth = (int) date('m');
        $currentYear = (int) date('Y');

        $expectedStart = new \DateTimeImmutable("{$currentYear}-{$currentMonth}-01 00:00:00");
        $expectedEnd = $expectedStart->modify('first day of next month');

        $stripeClientMock = $this->createMock(StripeClient::class);
        $cacheMock = $this->createMock(CacheInterface::class);

        $mockTransaction = $this->createMock(BalanceTransaction::class);
        $mockTransaction->method('__get')
            ->willReturnCallback(fn ($property): ?int => match ($property) {
                'amount' => 10000,
                'fee' => 500,
                'net' => 9500,
                default => null,
            });

        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->method('autoPagingIterator')
            ->willReturn(new \ArrayIterator([$mockTransaction]));

        $balanceTransactionServiceMock = $this->createMock(BalanceTransactionService::class);
        $balanceTransactionServiceMock->expects($this->once())
            ->method('all')
            ->with([
                'created' => [
                    'gte' => $expectedStart->getTimestamp(),
                    'lt' => $expectedEnd->getTimestamp(),
                ],
                'type' => 'charge',
                'limit' => 100,
            ])
            ->willReturn($collectionMock);

        $stripeClientMock->method('__get')
            ->with('balanceTransactions')
            ->willReturn($balanceTransactionServiceMock);

        // Assert: cache->get() should NEVER be called for current month
        $cacheMock->expects($this->never())
            ->method('get');

        $service = new BalanceStripeService($stripeClientMock, $cacheMock);

        $result = $service->getBalance($currentMonth, $currentYear);

        $this->assertIsArray($result);
        $this->assertEquals('100.00', $result['gross']);
    }

    public function testGetBalanceUsesCacheForPastMonth(): void
    {
        $pastMonth = 1;
        $pastYear = 2025;

        $expectedStart = new \DateTimeImmutable('2025-01-01 00:00:00');
        $expectedEnd = $expectedStart->modify('first day of next month');

        $stripeClientMock = $this->createMock(StripeClient::class);
        $cacheMock = $this->createMock(CacheInterface::class);

        $mockTransaction = $this->createMock(BalanceTransaction::class);
        $mockTransaction->method('__get')
            ->willReturnCallback(fn ($property): ?int => match ($property) {
                'amount' => 20000,
                'fee' => 1000,
                'net' => 19000,
                default => null,
            });

        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->method('autoPagingIterator')
            ->willReturn(new \ArrayIterator([$mockTransaction]));

        $balanceTransactionServiceMock = $this->createMock(BalanceTransactionService::class);
        $balanceTransactionServiceMock->expects($this->once())
            ->method('all')
            ->with([
                'created' => [
                    'gte' => $expectedStart->getTimestamp(),
                    'lt' => $expectedEnd->getTimestamp(),
                ],
                'type' => 'charge',
                'limit' => 100,
            ])
            ->willReturn($collectionMock);

        $stripeClientMock->method('__get')
            ->with('balanceTransactions')
            ->willReturn($balanceTransactionServiceMock);

        // Assert: cache->get() SHOULD be called for past month
        $cacheMock->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('stripe_balance_2025-01'),
                $this->anything()
            )
            ->willReturnCallback(fn ($key, $callback) => $callback());

        $service = new BalanceStripeService($stripeClientMock, $cacheMock);

        $result = $service->getBalance($pastMonth, $pastYear);

        $this->assertIsArray($result);
        $this->assertEquals('200.00', $result['gross']);
    }
}
