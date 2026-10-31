<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Previews;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\Previews\Quantities;

final class QuantitiesTest extends TestCase
{
    public function testCountsJoinIntoASentenceWithoutTheZeros(): void
    {
        self::assertSame(
            '3 productions, 1 picture and 2 tunes',
            Quantities::phrase([[3, 'production', 'productions'], [0, 'alias', 'aliases'], [1, 'picture', 'pictures'], [2, 'tune', 'tunes']]),
        );
    }

    public function testOneCountStandsAlone(): void
    {
        self::assertSame('1 alias', Quantities::phrase([[1, 'alias', 'aliases']]));
    }

    public function testNothingCountedIsNoPhrase(): void
    {
        self::assertNull(Quantities::phrase([[0, 'tune', 'tunes']]));
    }
}
