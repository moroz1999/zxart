<?php

declare(strict_types=1);

namespace ZxArt\Tests\Strings;

use PHPUnit\Framework\TestCase;
use ZxArt\Strings\LanguageDetector;

final class LanguageDetectorTest extends TestCase
{
    private LanguageDetector $sut;

    protected function setUp(): void
    {
        $this->sut = new LanguageDetector();
    }

    public function testPureEnglishReturnsEng(): void
    {
        $this->assertSame('eng', $this->sut->detectLanguage('Hello world this is a test sentence'));
    }

    public function testPureRussianReturnsRus(): void
    {
        $this->assertSame('rus', $this->sut->detectLanguage('Привет мир это тестовое предложение'));
    }

    public function testPureSpanishReturnsSpa(): void
    {
        $this->assertSame('spa', $this->sut->detectLanguage('áéíóúüñ áéíóúüñ áéíóúüñ áéíóúüñ áéíóúüñ'));
    }

    public function testEmptyStringReturnsNull(): void
    {
        $this->assertNull($this->sut->detectLanguage(''));
    }

    public function testDigitsOnlyReturnsNull(): void
    {
        $this->assertNull($this->sut->detectLanguage('1234567890'));
    }

    public function testMixedEnglishAndRussianBelowThresholdReturnsNull(): void
    {
        // roughly 50/50
        $this->assertNull($this->sut->detectLanguage('Hello Привет Hello Привет Hello Привет'));
    }

    public function testHtmlTagsStrippedBeforeDetection(): void
    {
        $this->assertSame('eng', $this->sut->detectLanguage('<p>Hello world this is a test sentence</p>'));
    }

    public function testOverwhelminglyEnglishWithFewRussianCharsReturnsEng(): void
    {
        // 95% English, 5% Russian — should still be 'eng'
        $this->assertSame('eng', $this->sut->detectLanguage('Hello world this is a test sentence with a bit of ф'));
    }
}
