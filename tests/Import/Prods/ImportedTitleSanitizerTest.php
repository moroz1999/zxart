<?php

declare(strict_types=1);

namespace ZxArt\Tests\Import\Prods;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Import\Prods\ImportedTitleSanitizer;

final class ImportedTitleSanitizerTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function titles(): iterable
    {
        yield 'article after a comma and a space' => ['Hobbit, The', 'The Hobbit'];
        yield 'article right after a comma' => ['Hobbit,The', 'The Hobbit'];
        yield 'another language' => ['Abadia del Crimen, La', 'La Abadia del Crimen'];
        yield 'no article' => ['Elite', 'Elite'];
        yield 'comma without an article' => ['Bats, Balls', 'Bats, Balls'];
    }

    #[DataProvider('titles')]
    public function testTheArticleMovesToTheFront(string $title, string $expected): void
    {
        self::assertSame($expected, (new ImportedTitleSanitizer())->sanitize($title));
    }
}
