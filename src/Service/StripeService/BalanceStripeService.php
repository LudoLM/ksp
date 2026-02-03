<?php

namespace App\Service\StripeService;

use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;

class BalanceStripeService
{
    public function __construct(
        private readonly StripeClient $stripeClient,
        #[Autowire(service: 'cache.stripe_reports')]
        private readonly CacheInterface $cache,
    ) {
    }

    public function getBalance(int $month, int $year): array
    {
        $yearMonth = sprintf('%04d-%02d', $year, $month);
        $cacheKey = 'stripe_balance_'.$yearMonth;

        $currentYearMonth = date('Y-m');
        if ($yearMonth === $currentYearMonth) {
            return $this->fetchBalanceFromStripe($month, $year);
        }

        return $this->cache->get($cacheKey, fn (): array => $this->fetchBalanceFromStripe($month, $year));
    }

    private function fetchBalanceFromStripe(int $month, int $year): array
    {
        $start = new \DateTimeImmutable("{$year}-{$month}-01 00:00:00");
        $end = $start->modify('first day of next month');

        $balanceTransactions = $this->stripeClient->balanceTransactions->all([
            'created' => [
                'gte' => $start->getTimestamp(),
                'lt' => $end->getTimestamp(),
            ],
            'type' => 'charge',
            'limit' => 100,
        ]);

        $totals = ['gross' => 0, 'fees' => 0, 'net' => 0, 'count' => 0];

        foreach ($balanceTransactions->autoPagingIterator() as $transaction) {
            $totals['gross'] += $transaction->amount;
            $totals['fees'] += $transaction->fee;
            $totals['net'] += $transaction->net;
            ++$totals['count'];
        }
        $gross = number_format($totals['gross'] / 100, 2, '.', '');
        $fees = number_format($totals['fees'] / 100, 2, '.', '');
        $ht = number_format($gross / 1.20, 2, '.', '');
        $tva = number_format($gross - $ht, 2, '.', '');
        $netStripe = number_format($gross - $fees, 2, '.', '');

        return [
            'gross' => $gross,
            'fees' => $fees,
            'ht' => $ht,
            'tva' => $tva,
            'netStripe' => $netStripe,
        ];
    }
}
