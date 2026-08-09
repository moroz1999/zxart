<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use PHPUnit\Framework\TestCase;
use ZxArt\Releases\Services\EmulatorResolverService;

/**
 * Emulator selection, and in particular what it does with hardware a release did
 * not state itself.
 *
 * The service is handed a release's *effective* set, so after the prod/release
 * split it routinely sees codes that live on the production. That is deliberate —
 * a release repeating nothing still has to resolve a machine — but it means the
 * production can decide a release's playability, which the tests below pin down.
 */
class EmulatorResolverServiceTest extends TestCase
{
    private EmulatorResolverService $service;

    protected function setUp(): void
    {
        $this->service = new EmulatorResolverService();
    }

    public function testASpectrumReleaseResolvesFromItsFormatAlone(): void
    {
        // no hardware at all: the fallback matches on the extension, which is why
        // a release carrying no codes is still playable
        $this->assertSame('usp', $this->service->resolveEmulator([], ['tap']));
    }

    public function testTheMachineDoesNotHaveToBeStatedForASpectrumRelease(): void
    {
        $this->assertSame('usp', $this->service->resolveEmulator(['zx48'], ['tap']));
        $this->assertSame('usp', $this->service->resolveEmulator(['zx128'], ['tap']));
    }

    public function testAMachineOfItsOwnFamilyWins(): void
    {
        $this->assertSame('samcoupe', $this->service->resolveEmulator(['samcoupe'], ['dsk']));
        $this->assertSame('zx81', $this->service->resolveEmulator(['zx811'], ['p']));
        $this->assertSame('tsconf', $this->service->resolveEmulator(['tsconf'], ['spg']));
    }

    /**
     * Observed on production 598457 ("Hi-Color Hero+"): both originals require a
     * General Sound, so the migration puts `gs` on the production, and release
     * 601040 — which states no hardware of its own — inherits it and stops being
     * playable where it was playable before.
     *
     * That is the intended reading rather than a regression: the previous answer
     * rested on the release saying nothing, not on it being GS-free. Six releases
     * catalogue-wide are in this position.
     */
    public function testUnsupportedHardwareSuppressesTheEmulatorEvenWhenInherited(): void
    {
        $this->assertSame('usp', $this->service->resolveEmulator([], ['tap']));
        $this->assertNull($this->service->resolveEmulator(['gs'], ['tap']));
        $this->assertNull($this->service->resolveEmulator(['ay', 'gs', 'pentagon2666'], ['tap', 'scl']));
    }

    public function testAFormatNoEmulatorHandlesResolvesToNothing(): void
    {
        $this->assertNull($this->service->resolveEmulator(['zx48'], ['rom']));
    }
}
