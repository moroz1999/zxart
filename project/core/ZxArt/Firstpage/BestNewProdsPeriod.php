<?php

declare(strict_types=1);

namespace ZxArt\Firstpage;

use DateTimeImmutable;

/**
 * Years the first page's best new demos and games are taken from: the current
 * year, and in January the previous one too, so the blocks are not empty while
 * the new year has barely started.
 */
final readonly class BestNewProdsPeriod
{
    private const int JANUARY = 1;

    public static function firstYear(DateTimeImmutable $today): int
    {
        $year = (int)$today->format('Y');
        $isJanuary = (int)$today->format('n') === self::JANUARY;

        return $isJanuary ? $year - 1 : $year;
    }
}
