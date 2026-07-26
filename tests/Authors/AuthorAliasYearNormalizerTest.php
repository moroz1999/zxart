<?php

declare(strict_types=1);

namespace ZxArt\Tests\Authors;

use PHPUnit\Framework\TestCase;
use ZxArt\Authors\Services\AuthorAliasYearNormalizer;

final class AuthorAliasYearNormalizerTest extends TestCase
{
    public function testConvertsYearToUnambiguousStorageDate(): void
    {
        self::assertSame('01.01.2010', (new AuthorAliasYearNormalizer())->toStorageDate('2010'));
    }

    public function testConvertsStoredDateToFormYear(): void
    {
        self::assertSame('2010', (new AuthorAliasYearNormalizer())->toFormYear('01.01.2010'));
    }

    public function testKeepsEmptyAndLegacyValuesUnchanged(): void
    {
        $normalizer = new AuthorAliasYearNormalizer();

        self::assertSame('', $normalizer->toStorageDate(''));
        self::assertSame('01.06.2010', $normalizer->toStorageDate('01.06.2010'));
        self::assertSame('', $normalizer->toFormYear(''));
    }
}
