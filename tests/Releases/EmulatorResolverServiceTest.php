<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareGroup;
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
#[AllowMockObjectsWithoutExpectations]
class EmulatorResolverServiceTest extends TestCase
{
    private EmulatorResolverService $service;

    protected function setUp(): void
    {
        $catalog = $this->createMock(HardwareCatalogService::class);
        $catalog->method('getCategoryOf')->willReturnCallback(
            static fn(string $code): ?HardwareGroup => match (true) {
                in_array($code, ['ay', 'beeper', 'gs', 'ngs', 'ts'], true) => HardwareGroup::SOUND,
                in_array($code, ['zx48', 'zx128', 'pentagon2666', 'timex2048', 'timex2068', 'samcoupe', 'tsconf', 'zx811'], true) => HardwareGroup::COMPUTERS,
                in_array($code, ['tape'], true) => HardwareGroup::STORAGE,
                default => null,
            },
        );

        $this->service = new EmulatorResolverService($catalog);
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
     * General Sound is the one thing the online emulators cannot produce, so a
     * release that has no other way to be heard is not offered at all.
     */
    public function testAReleaseWhoseOnlySoundIsUnsupportedIsNotPlayable(): void
    {
        $this->assertSame('usp', $this->service->resolveEmulator([], ['tap']));
        $this->assertNull($this->service->resolveEmulator(['gs'], ['tap']));
        $this->assertNull($this->service->resolveEmulator(['gs', 'pentagon2666'], ['tap', 'scl']));
    }

    /**
     * Any other sound in the set is a way for the release to be heard, so the
     * unsupported one only costs its own track. This is what the effective set
     * routinely produces: release 598464 states no sound of its own and inherits
     * `ay`, `gs` and `ngs` together from production 598457 ("Hi-Color Hero+").
     */
    public function testUnsupportedSoundAlongsideOtherSoundKeepsTheReleasePlayable(): void
    {
        $this->assertSame('usp', $this->service->resolveEmulator(['ay', 'gs', 'pentagon2666'], ['tap', 'scl']));
        $this->assertSame(
            'timex2048',
            $this->service->resolveEmulator(['timex2048', 'timex2068', 'pentagon2666', 'tape', 'ay', 'gs', 'ngs'], ['tap']),
        );
    }

    /**
     * A Timex is a Spectrum by format, so the USP fallback would swallow it —
     * only the machine says the SCLD video modes have to be emulated, and each
     * model is its own emulator id because JSSpeccy boots one machine.
     */
    public function testATimexMachineWinsOverTheSpectrumFallback(): void
    {
        $this->assertSame('timex2048', $this->service->resolveEmulator(['timex2048'], ['tap']));
        $this->assertSame('timex2068', $this->service->resolveEmulator(['timex2068'], ['tzx']));
        $this->assertSame('timex2048', $this->service->resolveEmulator(['zx48', 'timex2048'], ['z80']));
    }

    /**
     * Cartridges are the Timex-only format, and JSSpeccy cannot load them, so a
     * release distributed as one alone stays unplayable.
     */
    public function testATimexCartridgeIsNotPlayable(): void
    {
        $this->assertNull($this->service->resolveEmulator(['timex2068'], ['dck']));
    }

    public function testAFormatNoEmulatorHandlesResolvesToNothing(): void
    {
        $this->assertNull($this->service->resolveEmulator(['zx48'], ['rom']));
    }
}
