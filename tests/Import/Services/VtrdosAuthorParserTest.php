<?php

declare(strict_types=1);

namespace ZxArt\Tests\Import\Services;

use PHPUnit\Framework\TestCase;
use ZxArt\Import\Services\VtrdosAuthorParser;

final class VtrdosAuthorParserTest extends TestCase
{
    private VtrdosAuthorParser $sut;

    protected function setUp(): void
    {
        $this->sut = new VtrdosAuthorParser();
    }

    private function callParseInfoCell(string $text, array $roles = []): array
    {
        $prodYear = null;
        $releaseYear = null;
        $labels = [];
        $groups = [];
        $publishers = [];
        $undetermined = [];
        $releaseType = null;
        $this->sut->parseInfoCell($text, $roles, $prodYear, $releaseYear, $labels, $groups, $publishers, $undetermined, $releaseType);
        return compact('prodYear', 'releaseYear', 'labels', 'groups', 'publishers', 'undetermined', 'releaseType');
    }

    // --- parseInfoCell ---

    public function testEmptyTextProducesNoOutput(): void
    {
        $r = $this->callParseInfoCell('');
        $this->assertNull($r['prodYear']);
        $this->assertNull($r['releaseYear']);
        $this->assertSame([], $r['labels']);
        $this->assertSame([], $r['publishers']);
    }

    public function testNaTextProducesNoOutput(): void
    {
        $r = $this->callParseInfoCell('n/a');
        $this->assertSame([], $r['labels']);
    }

    public function testSingleEntryBecomesPublisher(): void
    {
        $r = $this->callParseInfoCell('Acme');
        $this->assertSame(['Acme'], $r['publishers']);
        $this->assertSame([], $r['groups']);
    }

    public function testSingleEntryWithTwoDigitYearAbove50(): void
    {
        $r = $this->callParseInfoCell("Acme'86");
        $this->assertSame(1986, $r['releaseYear']);
        $this->assertSame(1986, $r['prodYear']);
    }

    public function testSingleEntryWithTwoDigitYearUpTo50(): void
    {
        $r = $this->callParseInfoCell("Acme'05");
        $this->assertSame(2005, $r['releaseYear']);
        $this->assertSame(2005, $r['prodYear']);
    }

    public function testSingleEntryWithFourDigitYear(): void
    {
        $r = $this->callParseInfoCell("Acme'1999");
        $this->assertSame(1999, $r['releaseYear']);
    }

    public function testTwoEntriesFirstIsGroupSecondIsPublisher(): void
    {
        $r = $this->callParseInfoCell('BestGroup, Acme');
        $this->assertSame(['BestGroup'], $r['groups']);
        $this->assertSame(['Acme'], $r['publishers']);
    }

    public function testTwoEntriesYearsAssignedCorrectly(): void
    {
        $r = $this->callParseInfoCell("BestGroup'88, Acme'90");
        $this->assertSame(1988, $r['prodYear']);
        $this->assertSame(1990, $r['releaseYear']);
    }

    public function testGroupYearMissingFallsBackToPublisherYear(): void
    {
        $r = $this->callParseInfoCell("BestGroup, Acme'90");
        $this->assertSame(1990, $r['prodYear']);
        $this->assertSame(1990, $r['releaseYear']);
    }

    public function testExtraEntriesGoToUndetermined(): void
    {
        $roles = ['code'];
        $r = $this->callParseInfoCell('Group, Publisher, Extra', $roles);
        $this->assertArrayHasKey('Extra', $r['undetermined']);
        $this->assertSame($roles, $r['undetermined']['Extra']);
    }

    public function testByPrefixStripped(): void
    {
        $r = $this->callParseInfoCell('by Acme');
        $this->assertSame(['Acme'], $r['publishers']);
    }

    public function testDashSeparatorStripsTrailingPart(): void
    {
        $r = $this->callParseInfoCell('Acme - some notes');
        $this->assertSame(['Acme'], $r['publishers']);
    }

    public function testLabelsContainAllEntries(): void
    {
        $r = $this->callParseInfoCell('Group, Publisher, Extra');
        $labelNames = array_map(fn($l) => $l->id, $r['labels']);
        $this->assertContains('Group', $labelNames);
        $this->assertContains('Publisher', $labelNames);
        $this->assertContains('Extra', $labelNames);
    }

    // --- parseVersionCell ---

    public function testAuthorKeywordSetsOriginalReleaseType(): void
    {
        $releaseYear = null;
        $labels = [];
        $undetermined = [];
        $releaseType = null;
        $this->sut->parseVersionCell('author', [], $releaseYear, $labels, $undetermined, $releaseType);
        $this->assertSame('original', $releaseType);
        $this->assertSame([], $labels);
        $this->assertSame([], $undetermined);
    }

    public function testAuthorKeywordCaseInsensitive(): void
    {
        $releaseYear = null;
        $labels = [];
        $undetermined = [];
        $releaseType = null;
        $this->sut->parseVersionCell('AUTHOR', [], $releaseYear, $labels, $undetermined, $releaseType);
        $this->assertSame('original', $releaseType);
    }

    public function testVersionCellEntryGoesToUndetermined(): void
    {
        $roles = ['translation'];
        $releaseYear = null;
        $labels = [];
        $undetermined = [];
        $releaseType = null;
        $this->sut->parseVersionCell('Translator', $roles, $releaseYear, $labels, $undetermined, $releaseType);
        $this->assertArrayHasKey('Translator', $undetermined);
        $this->assertNull($releaseType);
    }

    public function testVersionCellYearExtracted(): void
    {
        $releaseYear = null;
        $labels = [];
        $undetermined = [];
        $releaseType = null;
        $this->sut->parseVersionCell("Translator'99", [], $releaseYear, $labels, $undetermined, $releaseType);
        $this->assertSame(1999, $releaseYear);
    }

    public function testVersionCellNaProducesNoOutput(): void
    {
        $releaseYear = null;
        $labels = [];
        $undetermined = [];
        $releaseType = null;
        $this->sut->parseVersionCell('n/a', [], $releaseYear, $labels, $undetermined, $releaseType);
        $this->assertSame([], $labels);
        $this->assertSame([], $undetermined);
    }

    public function testVersionCellFirstYearWins(): void
    {
        $releaseYear = null;
        $labels = [];
        $undetermined = [];
        $releaseType = null;
        $this->sut->parseVersionCell("A'90, B'95", [], $releaseYear, $labels, $undetermined, $releaseType);
        $this->assertSame(1990, $releaseYear);
    }
}
