<?php

declare(strict_types=1);

namespace ZxArt\Tests\Import\Services;

use PHPUnit\Framework\TestCase;
use ZxArt\Import\Services\VtrdosHardwareProvider;
use ZxArt\Import\Services\VtrdosTitleParser;
use ZxArt\Releases\ReleaseTypes;

final class VtrdosTitleParserTest extends TestCase
{
    private VtrdosTitleParser $sut;

    protected function setUp(): void
    {
        $this->sut = new VtrdosTitleParser(new VtrdosHardwareProvider());
    }

    public function testPlainTitlePassesThrough(): void
    {
        $r = $this->sut->parse('My Game');
        $this->assertSame('My Game', $r->title);
        $this->assertNull($r->languages);
        $this->assertNull($r->releaseType);
        $this->assertNull($r->version);
        $this->assertSame([], $r->hardwareRequired);
    }

    public function testSingleLanguageMarkerExtracted(): void
    {
        $r = $this->sut->parse('My Game (rus)');
        $this->assertSame('My Game', trim($r->title));
        $this->assertSame(['ru'], $r->languages);
        $this->assertSame(ReleaseTypes::localization->value, $r->releaseType);
    }

    public function testMultipleLanguagesInOneGroup(): void
    {
        $r = $this->sut->parse('My Game (eng/rus)');
        $this->assertContains('en', $r->languages);
        $this->assertContains('ru', $r->languages);
    }

    public function testModMarkerSetsReleaseType(): void
    {
        $r = $this->sut->parse('My Game (mod)');
        $this->assertSame(ReleaseTypes::mod->value, $r->releaseType);
        $this->assertNull($r->languages);
    }

    public function testBugfixMarkerSetsAdaptationType(): void
    {
        $r = $this->sut->parse('My Game (bugfix)');
        $this->assertSame(ReleaseTypes::adaptation->value, $r->releaseType);
    }

    public function testModBeatsLocalizationByPriority(): void
    {
        // mod priority=100 beats localization which is set when languages found
        $r = $this->sut->parse('My Game (rus,mod)');
        $this->assertSame(ReleaseTypes::mod->value, $r->releaseType);
    }

    public function testVersionExtracted(): void
    {
        $r = $this->sut->parse('My Game v1.5');
        $this->assertSame('1.5', $r->version);
        $this->assertSame('My Game', $r->title);
    }

    public function testVersionWithLanguage(): void
    {
        $r = $this->sut->parse('My Game (rus) v2.0');
        $this->assertSame('2.0', $r->version);
        $this->assertSame(['ru'], $r->languages);
    }

    public function testDemoSuffixStripped(): void
    {
        $r = $this->sut->parse('My Game Demo');
        $this->assertSame('My Game', $r->title);
    }

    public function testDemoSuffixCaseInsensitive(): void
    {
        $r = $this->sut->parse('My Game DEMO');
        $this->assertSame('My Game', $r->title);
    }

    public function testTechnicalMarkerTxtStripped(): void
    {
        $r = $this->sut->parse('My Game (txt)');
        $this->assertSame('My Game', trim($r->title));
        $this->assertNull($r->languages);
        $this->assertNull($r->releaseType);
    }

    public function testUnknownBracketsStrippedFromTitle(): void
    {
        // cleanupBaseTitle removes all remaining bracket content from the title
        $r = $this->sut->parse('My Game (special edition)');
        $this->assertSame('My Game', trim($r->title));
        $this->assertNull($r->languages);
        $this->assertNull($r->releaseType);
    }

    public function testHardwareMarkerExtracted(): void
    {
        $r = $this->sut->parse('My Game (for 48k)');
        $this->assertContains('zx48', $r->hardwareRequired);
    }

    public function testHardwareAndLanguageTogether(): void
    {
        $r = $this->sut->parse('My Game (for 48k) (rus)');
        $this->assertContains('zx48', $r->hardwareRequired);
        $this->assertSame(['ru'], $r->languages);
    }

    public function testLeadingTrailingSpacesTrimmed(): void
    {
        $r = $this->sut->parse('  My Game  ');
        $this->assertSame('My Game', $r->title);
    }

    public function testMultipleSpacesCollapsed(): void
    {
        $r = $this->sut->parse('My  Game');
        $this->assertSame('My Game', $r->title);
    }
}
