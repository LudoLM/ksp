<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Helper\SaisonHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SaisonHelper::class)]
class SaisonHelperTest extends TestCase
{
    #[DataProvider('dateProvider')]
    public function testCurrentReturnsExpectedSaison(string $date, string $expected): void
    {
        $now = new \DateTimeImmutable($date);

        $this->assertSame($expected, SaisonHelper::current($now));
    }

    public static function dateProvider(): \Generator
    {
        yield 'debut de saison en septembre' => ['2026-09-01', '2026-2027'];
        yield 'decembre' => ['2026-12-15', '2026-2027'];
        yield 'mars de la meme saison' => ['2027-03-15', '2026-2027'];
        yield 'dernier jour de la saison (31 aout)' => ['2027-08-31', '2026-2027'];
        yield 'premier jour de la saison suivante' => ['2027-09-01', '2027-2028'];
    }
}
