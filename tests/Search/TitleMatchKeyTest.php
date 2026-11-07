<?php

declare(strict_types=1);

namespace ZxArt\Tests\Search;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Search\Dbal\TitleMatch;

/**
 * The key a title and a phrase are compared by: lower case, letters and
 * digits of any script only, HTML entities left out.
 */
final class TitleMatchKeyTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function texts(): array
    {
        return [
            'case' => ['Rebelstar', 'rebelstar'],
            'hyphen' => ['Rebel-Star', 'rebelstar'],
            'spaces and punctuation' => ['  Rebel, Star!  ', 'rebelstar'],
            'digits' => ['Game 123', 'game123'],
            'cyrillic' => ['Ёлка-2', 'ёлка2'],
            'latin letters beyond a-z' => ['Zażółć Gęślą', 'zażółćgęślą'],
            'symbols' => ['a–b©c', 'abc'],
            'html entities' => ['AT&amp;T &#039;93', 'att93'],
            'nothing but punctuation' => [' -_%\\ ', ''],
            'empty' => ['', ''],
        ];
    }

    #[DataProvider('texts')]
    public function testTheKeyKeepsLettersAndDigitsInLowerCase(string $text, string $key): void
    {
        self::assertSame($key, (new TitleMatch())->key($text));
    }
}
