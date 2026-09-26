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
                in_array($code, ['zx48', 'zx128', 'pentagon128', 'pentagon2666', 'timex2048', 'timex2068', 'samcoupe', 'tsconf', 'zx811', 'atm', 'atm2', 'baseconf', 'scorpion', 'profi', 'sprinter'], true) => HardwareGroup::COMPUTERS,
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
     * General Sound plays on the machines carrying a NeoGS and nowhere else, so
     * a release resolving to one of the others and having no second way to be
     * heard is not offered at all.
     */
    public function testAReleaseWhoseOnlySoundIsUnsupportedIsNotPlayable(): void
    {
        $this->assertSame('usp', $this->service->resolveEmulator([], ['tap']));
        // A tape reaches no machine here that has a NeoGS, so there is nowhere
        // for the track to be heard.
        $this->assertNull($this->service->resolveEmulator(['gs'], ['tap']));
    }

    /**
     * The machines MAME runs carry a NeoGS, so a General Sound track is the
     * reason to play them rather than a reason to hide them.
     */
    public function testAGeneralSoundReleaseIsPlayableOnAMachineCarryingANeoGs(): void
    {
        $this->assertSame('scorpion', $this->service->resolveEmulator(['scorpion', 'gs'], ['trd']));
        $this->assertSame('tsconf', $this->service->resolveEmulator(['tsconf', 'gs'], ['spg']));
        $this->assertSame('profi', $this->service->resolveEmulator(['profi', 'ngs'], ['scl']));
    }

    /**
     * A General Sound disk that named no machine of its own, or named a plain
     * Spectrum clone, is Spectrum software with a sound card — so it goes to
     * the Scorpion GMX rather than to the fallback, which has no General Sound.
     */
    public function testAGeneralSoundDiskWithoutAMachineGoesToTheScorpion(): void
    {
        $this->assertSame('scorpion', $this->service->resolveEmulator(['gs'], ['trd']));
        $this->assertSame('scorpion', $this->service->resolveEmulator(['ngs'], ['scl']));
        $this->assertSame('scorpion', $this->service->resolveEmulator(['pentagon128', 'gs'], ['trd']));
    }

    /**
     * A TurboSound disk goes the same way: the Spectrum fallback has no second
     * AY, and a machine that can be fitted with one plays the whole track.
     */
    public function testATurboSoundDiskWithoutAMachineGoesToTheScorpion(): void
    {
        $this->assertSame('scorpion', $this->service->resolveEmulator(['ts'], ['trd']));
        $this->assertSame('scorpion', $this->service->resolveEmulator(['pentagon128', 'ay', 'ts'], ['scl']));
        // A machine of its own still wins, and takes a second AY there.
        $this->assertSame('atm', $this->service->resolveEmulator(['atm2', 'ts'], ['trd']));
        // A snapshot the snapshot device runs on its own, so that reaches MAME too.
        $this->assertSame('scorpion', $this->service->resolveEmulator(['ts'], ['z80']));
        // A tape MAME cannot start unattended, so the fallback keeps it.
        $this->assertSame('usp', $this->service->resolveEmulator(['ts'], ['tap']));
    }

    /**
     * A machine of its own still wins, and what happens next is the machine's
     * own doing: the ZX Evolution carries a NeoGS and plays the track, while
     * the ATM Turbo has no ZX Bus to put one on — so a release whose only
     * sound is General Sound would run mute there and is not offered, exactly
     * as the sound rule has always said.
     */
    public function testAMachineOfItsOwnWinsOverTheGeneralSoundFallback(): void
    {
        $this->assertSame('pentevo', $this->service->resolveEmulator(['baseconf', 'gs'], ['scl']));
        // The ATM has no ZX Bus, so an ATM release that wants a General Sound
        // runs on the compatible ZX Evolution, which has one.
        $this->assertSame('pentevo', $this->service->resolveEmulator(['atm2', 'gs'], ['trd']));
        $this->assertSame('pentevo', $this->service->resolveEmulator(['atm2', 'ay', 'gs'], ['trd']));
        $this->assertSame('atm', $this->service->resolveEmulator(['atm2', 'ay'], ['trd']));
    }

    /**
     * Any other sound in the set is a way for the release to be heard, so the
     * unsupported one only costs its own track. This is what the effective set
     * routinely produces: release 598464 states no sound of its own and inherits
     * `ay`, `gs` and `ngs` together from production 598457 ("Hi-Color Hero+").
     */
    public function testUnsupportedSoundAlongsideOtherSoundKeepsTheReleasePlayable(): void
    {
        // On a disk it does better than stay playable: the Scorpion GMX plays
        // the AY and the General Sound both.
        $this->assertSame('scorpion', $this->service->resolveEmulator(['ay', 'gs', 'pentagon128'], ['tap', 'scl']));
        $this->assertSame(
            'timex2048',
            $this->service->resolveEmulator(['timex2048', 'timex2068', 'pentagon128', 'tape', 'ay', 'gs', 'ngs'], ['tap']),
        );
    }

    /**
     * A TR-DOS disk is a Spectrum release by format, so the Spectrum fallback
     * would swallow it — only the machine says the memory, video and disk
     * system of that machine have to be emulated.
     */
    public function testAMachineOfItsOwnWinsOverTheSpectrumFallbackForADisk(): void
    {
        $this->assertSame('pentevo', $this->service->resolveEmulator(['baseconf'], ['trd']));
        $this->assertSame('atm', $this->service->resolveEmulator(['atm'], ['trd']));
        $this->assertSame('atm', $this->service->resolveEmulator(['atm2'], ['scl']));
        $this->assertSame('scorpion', $this->service->resolveEmulator(['scorpion'], ['scl']));
        $this->assertSame('sprinter', $this->service->resolveEmulator(['sprinter'], ['trd']));
    }

    /**
     * These machines start a disk or a snapshot by themselves. A tape they
     * cannot: MAME would come up at BASIC with it in the deck, so the release
     * goes to the Spectrum fallback, which loads it.
     */
    public function testAMachineOfItsOwnTakesADiskOrASnapshotButNotATape(): void
    {
        $this->assertSame('scorpion', $this->service->resolveEmulator(['scorpion'], ['z80']));
        $this->assertSame('atm', $this->service->resolveEmulator(['atm'], ['sna']));
        $this->assertSame('usp', $this->service->resolveEmulator(['atm'], ['tap']));
    }

    /**
     * The Pentagon 2.666 is what nothing here emulates, so a release that runs
     * only there is not offered — and where it also names a machine that can
     * be emulated, only the 2.666 version is out of reach.
     */
    public function testAReleaseOnlyForAnUnsupportedMachineIsNotPlayable(): void
    {
        $this->assertNull($this->service->resolveEmulator(['pentagon2666'], ['trd']));
        $this->assertNull($this->service->resolveEmulator(['pentagon2666', 'ay'], ['tap', 'scl']));
        $this->assertSame('usp', $this->service->resolveEmulator(['pentagon2666', 'zx128'], ['tap']));
        $this->assertSame('tsconf', $this->service->resolveEmulator(['pentagon2666', 'tsconf'], ['spg']));
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

    /**
     * The Next boots NextZXOS off an SD card and the whole release is mounted on
     * it, so the machine alone decides here and no format can rule it out.
     * Whether the release holds anything startable is asked separately, by
     * zxReleaseElement.
     */
    public function testTheNextResolvesFromItsMachineWhateverTheFormat(): void
    {
        $this->assertSame('zxnext', $this->service->resolveEmulator(['zxnext'], ['nex']));
        $this->assertSame('zxnext', $this->service->resolveEmulator(['zxnext'], ['snx']));
        $this->assertSame('zxnext', $this->service->resolveEmulator(['zxnext'], ['tap']));
        $this->assertSame('zxnext', $this->service->resolveEmulator(['zxnext'], []));
    }

    /**
     * A tape on a Next is a Next release, so it must not fall through to the
     * Spectrum fallback: playing it there would run it on the wrong machine.
     */
    public function testANextTapeDoesNotFallThroughToTheSpectrum(): void
    {
        $this->assertSame('zxnext', $this->service->resolveEmulator(['zx128', 'zxnext'], ['tap']));
    }

    public function testOnlyWholeArchiveEmulatorsAreHandedTheReleaseFile(): void
    {
        $this->assertTrue($this->service->servesWholeArchive('usp'));
        $this->assertTrue($this->service->servesWholeArchive('zxnext'));
        $this->assertTrue($this->service->servesWholeArchive('tsconf'));
        $this->assertFalse($this->service->servesWholeArchive('samcoupe'));
        $this->assertFalse($this->service->servesWholeArchive(null));
    }

    /** What the Next is offered to start, ranked by zxReleaseElement. */
    public function testTheNextRunsWhatNextZxosCanStart(): void
    {
        $this->assertSame(
            ['nex', 'dot', 'bas', 'snx', 'b', 'tap', 'tzx'],
            $this->service->getRunnableTypesForEmulator('zxnext'),
        );
    }
}
