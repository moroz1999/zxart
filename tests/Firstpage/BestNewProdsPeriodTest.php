<?php

declare(strict_types=1);

namespace ZxArt\Tests\Firstpage;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Firstpage\BestNewProdsPeriod;

/**
 * The first page's best new demos and games come from the current year. In
 * January the year has barely started, so the previous year counts too.
 */
class BestNewProdsPeriodTest extends TestCase
{
    /**
     * @return array<string, array{string, int}>
     */
    public static function days(): array
    {
        return [
            'first of January takes the previous year too' => ['2026-01-01', 2025],
            'end of January takes the previous year too' => ['2026-01-31', 2025],
            'February takes the current year only' => ['2026-02-01', 2026],
            'December takes the current year only' => ['2026-12-31', 2026],
        ];
    }

    #[DataProvider('days')]
    public function testFirstYearShown(string $today, int $expectedYear): void
    {
        $this->assertSame($expectedYear, BestNewProdsPeriod::firstYear(new DateTimeImmutable($today)));
    }
}
