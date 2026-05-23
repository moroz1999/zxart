<?php

declare(strict_types=1);

namespace ZxArt\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use ZxArt\Helpers\AlphanumericColumnSearch;

final class AlphanumericColumnSearchTest extends TestCase
{
    private AlphanumericColumnSearch $sut;

    protected function setUp(): void
    {
        $this->sut = new AlphanumericColumnSearch();
    }

    public function testPlainEnglishLowercased(): void
    {
        $this->assertSame('hello', $this->sut->toAlphanumeric('Hello'));
    }

    public function testSpacesRemoved(): void
    {
        $this->assertSame('helloworld', $this->sut->toAlphanumeric('Hello World'));
    }

    public function testPunctuationRemoved(): void
    {
        $this->assertSame('helloworld', $this->sut->toAlphanumeric("Hello, World!"));
    }

    public function testCyrillicKept(): void
    {
        $this->assertSame('привет', $this->sut->toAlphanumeric('Привет'));
    }

    public function testDigitsKept(): void
    {
        $this->assertSame('game123', $this->sut->toAlphanumeric('Game 123'));
    }

    public function testLeadingTrailingSpacesTrimmedThenRemoved(): void
    {
        $this->assertSame('abc', $this->sut->toAlphanumeric('  abc  '));
    }

    public function testEmptyStringReturnsEmpty(): void
    {
        $this->assertSame('', $this->sut->toAlphanumeric(''));
    }

    public function testMixedUnicodeSymbolsRemoved(): void
    {
        $this->assertSame('abc', $this->sut->toAlphanumeric('a–b©c'));
    }
}
