<?php

declare(strict_types=1);

namespace ZxArt\Tests\Import\Services;

use PHPUnit\Framework\TestCase;
use ZxArt\Import\Services\VtrdosHardwareProvider;

final class VtrdosHardwareProviderTest extends TestCase
{
    private VtrdosHardwareProvider $sut;

    protected function setUp(): void
    {
        $this->sut = new VtrdosHardwareProvider();
    }

    public function testMatchReturnsBracketedMarker(): void
    {
        // (for 48k) also triggers the plain '48k' entry, so zx48 appears twice
        $result = $this->sut->match('Cool Game (for 48k)');
        $this->assertContains('zx48', $result);
    }

    public function testMatchReturnsPlainMarker(): void
    {
        $this->assertContains('zx48', $this->sut->match('48k game'));
    }

    public function testMatchReturnsEmptyWhenNoHardware(): void
    {
        $this->assertSame([], $this->sut->match('Plain Game Title'));
    }

    public function testMatchIsCaseInsensitive(): void
    {
        $this->assertContains('gs', $this->sut->match('Game (GS)'));
    }

    public function testMatch48And128kAddsZx48TwiceDeduped(): void
    {
        $result = $this->sut->match('Game (48/128k)');
        $this->assertContains('zx128', $result);
        $this->assertContains('zx48', $result);
    }

    public function testMatchMultipleHardwareMarkers(): void
    {
        $result = $this->sut->match('Game (gs) (sd)');
        $this->assertContains('gs', $result);
        $this->assertContains('soundrive', $result);
    }

    public function testRemoveMatchesRemovesBracketedMarkers(): void
    {
        $result = $this->sut->removeMatches('Cool Game (for 48k)');
        $this->assertStringNotContainsString('(for 48k)', $result);
    }

    public function testRemoveMatchesKeepsPlainMarkersWithoutBrackets(): void
    {
        // plain markers like "48k" have no brackets, so they are not removed
        $result = $this->sut->removeMatches('48k game');
        $this->assertStringContainsString('48k', $result);
    }

    public function testRemoveMatchesKeepsTitleText(): void
    {
        $result = $this->sut->removeMatches('My Game (for 48k) extra');
        $this->assertStringContainsString('My Game', $result);
        $this->assertStringContainsString('extra', $result);
    }
}
