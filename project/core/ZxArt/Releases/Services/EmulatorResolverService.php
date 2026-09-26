<?php
declare(strict_types=1);


namespace ZxArt\Releases\Services;

use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareGroup;

final class EmulatorResolverService
{
    /**
     * Hardware only some of the emulators can be. The NeoGS card covers both
     * General Sound codes. It is a sound extension, so it only decides
     * playability when it is the only sound there is — see
     * {@see isSilencedByUnsupportedHardware()}.
     */
    private const array UNSUPPORTED_HARDWARE = ['gs', 'ngs'];

    /**
     * The emulators carrying a NeoGS, which is what a General Sound soundtrack
     * plays on. Every one of them is a MAME machine with a ZX Bus to plug the
     * card into; the ATM Turbo has no such bus, and the Spectrum, ZX81, SAM and
     * Next emulators have no General Sound at all.
     */
    private const array GENERAL_SOUND_EMULATORS = ['tsconf', 'scorpion', 'profi', 'pentevo', 'sprinter'];

    /**
     * Sound only the MAME machines here can produce: the NeoGS, and the second
     * AY of a TurboSound. The Spectrum fallback has neither, so a release
     * wanting one is better off on a machine that can be fitted with it — see
     * {@see matchEmulator()}.
     */
    private const array MAME_SOUND_HARDWARE = ['gs', 'ngs', 'ts'];

    /**
     * Machines none of the cores here can be. Without this a release needing
     * one falls through to the Spectrum fallback on its format alone and is
     * offered as a machine it is not: the Pentagon 2.666 has memory, video and
     * a turbo of its own that neither MAME nor Unreal Speccy Portable
     * emulates.
     *
     * Like General Sound it only decides playability when no other machine is
     * in the set — see {@see runsOnlyOnUnsupportedMachine()}.
     */
    private const array UNSUPPORTED_MACHINES = ['pentagon2666'];

    /** What a TR-DOS machine here starts by itself: a disk, or a snapshot. */
    private const array TRDOS_MACHINE_EXTENSIONS = ['trd', 'scl', 'z80', 'sna'];

    /** Snapshots and tapes JSSpeccy loads; it has no cartridge (dck) support. */
    private const array JSSPECCY_EXTENSIONS = ['tap', 'tzx', 'z80', 'sna', 'szx'];

    private const array EMULATORS = [
        'zx80' => [
            'hardware' => ['zx80'],
            'extensions' => ['tzx', 'p', 'o'],
        ],
        'zx81' => [
            'hardware' => ['zx8116', 'zx811', 'zx812', 'zx8132', 'zx8164', 'lambda8300'],
            'extensions' => ['tzx', 'p', 'o', 'z81'],
        ],
        'tsconf' => [
            'hardware' => ['tsconf'],
            'extensions' => ['spg', 'img', 'trd', 'scl'],
        ],
        // The TR-DOS machines MAME runs. A disk is what they boot; a snapshot
        // the snapshot device loads into memory and runs on its own. A tape is
        // absent on purpose: MAME would come up at BASIC with the tape in the
        // deck and nothing to press play, so such a release is better off on
        // the Spectrum fallback, which loads it.
        'scorpion' => [
            'hardware' => ['scorpion', 'scorpion1024'],
            'extensions' => self::TRDOS_MACHINE_EXTENSIONS,
        ],
        'atm' => [
            'hardware' => ['atm', 'atm2'],
            'extensions' => self::TRDOS_MACHINE_EXTENSIONS,
        ],
        'profi' => [
            'hardware' => ['profi'],
            'extensions' => self::TRDOS_MACHINE_EXTENSIONS,
        ],
        // BaseConf is the ZX Evolution configuration MAME runs as `pentevo`
        'pentevo' => [
            'hardware' => ['baseconf', 'zxevolution'],
            'extensions' => self::TRDOS_MACHINE_EXTENSIONS,
        ],
        'sprinter' => [
            'hardware' => ['sprinter'],
            'extensions' => self::TRDOS_MACHINE_EXTENSIONS,
        ],
        'samcoupe' => [
            'hardware' => ['samcoupe'],
            'extensions' => ['tzx', 'tap', 'blk', 'mfi', 'dfi', 'mfm', 'td0', 'imd', '86f', 'd77', 'd88', 'ldd', 'cqm', 'cqi', 'dsk', 'mgt', 'sad', 'cpm'],
        ],
        'usp' => [
            'hardware' => [],
            'extensions' => ['trd', 'tap', 'z80', 'sna', 'tzx', 'scl'],
        ],
        // What NextZXOS itself can launch from its Browser. The release's
        // archive is the carrier, not a launch target, so no container type
        // belongs here — see {@see servesWholeArchive()}. `dot` is a NextZXOS
        // command, which its Browser runs from anywhere on the card. `snx` is
        // absent on purpose: it is a CSpect snapshot, which neither NextZXOS
        // nor the emulator core can load, and offering it gave a black screen.
        'zxnext' => [
            'hardware' => ['zxnext'],
            'extensions' => ['nex', 'dot', 'bas', 'snx', 'b', 'tap', 'tzx'],
        ],
        // JSSpeccy boots one machine, so each Timex model is its own emulator id
        'timex2048' => [
            'hardware' => ['timex2048'],
            'extensions' => self::JSSPECCY_EXTENSIONS,
        ],
        'timex2068' => [
            'hardware' => ['timex2068'],
            'extensions' => self::JSSPECCY_EXTENSIONS,
        ],
    ];

    public function __construct(
        private readonly HardwareCatalogService $catalogService,
    ) {
    }

    /**
     * @param string[] $hardwareRequired
     * @param string[] $releaseFormats
     */
    public function resolveEmulator(array $hardwareRequired, array $releaseFormats): ?string
    {
        if ($this->runsOnlyOnUnsupportedMachine($hardwareRequired)) {
            return null;
        }
        $emulator = $this->matchEmulator($hardwareRequired, $releaseFormats);
        if ($emulator === null) {
            return null;
        }
        // Which machine the release resolved to is what decides whether its
        // General Sound track can be heard, so the check comes after the
        // match and not before it.
        if (
            !in_array($emulator, self::GENERAL_SOUND_EMULATORS, true)
            && $this->isSilencedByUnsupportedHardware($hardwareRequired)
        ) {
            return null;
        }

        return $emulator;
    }

    /**
     * The machine the release runs on, by what it needs and what it is packed
     * as. The order is the priority: a machine naming itself wins over the
     * Spectrum fallback that any Spectrum format would also match.
     *
     * @param string[] $hardwareRequired
     * @param string[] $releaseFormats
     */
    private function matchEmulator(array $hardwareRequired, array $releaseFormats): ?string
    {
        if ($this->matchHardwareAndFormat($hardwareRequired, $releaseFormats, 'zx80')) {
            return 'zx80';
        }
        if ($this->matchHardwareAndFormat($hardwareRequired, $releaseFormats, 'zx81')) {
            return 'zx81';
        }
        if ($this->matchHardware($hardwareRequired, 'tsconf')) {
            return 'tsconf';
        }
        if ($this->matchHardwareAndFormat($hardwareRequired, $releaseFormats, 'samcoupe')) {
            return 'samcoupe';
        }
        // An ATM release with a General Sound track runs on the ZX Evolution
        // instead: the two are compatible, and the ATM Turbo is the one
        // machine here with no ZX Bus to put a NeoGS on.
        if (
            $this->matchHardwareAndFormat($hardwareRequired, $releaseFormats, 'atm')
            && array_intersect($hardwareRequired, self::UNSUPPORTED_HARDWARE)
        ) {
            return 'pentevo';
        }
        // The TR-DOS machines, each on its own disk. They come before the
        // Spectrum fallback for the same reason Timex does: their releases are
        // Spectrum releases by format, and only the machine says the memory,
        // video and disk system of that machine have to be emulated.
        foreach (['scorpion', 'atm', 'profi', 'pentevo', 'sprinter'] as $machine) {
            if ($this->matchHardwareAndFormat($hardwareRequired, $releaseFormats, $machine)) {
                return $machine;
            }
        }
        // Hardware alone, like TSConf: the Next boots NextZXOS off an SD card
        // and the whole release is mounted on it, so a release is playable
        // even when no single file is a launch target — the card is still
        // browsable. Which file to start is a recommendation, not a gate:
        // see zxReleaseElement::getLaunchFileId().
        if ($this->matchHardware($hardwareRequired, 'zxnext')) {
            return 'zxnext';
        }
        // Before the USP fallback: a Timex release is a Spectrum release by format,
        // and only its machine says the SCLD modes have to be emulated
        if ($this->matchHardwareAndFormat($hardwareRequired, $releaseFormats, 'timex2048')) {
            return 'timex2048';
        }
        if ($this->matchHardwareAndFormat($hardwareRequired, $releaseFormats, 'timex2068')) {
            return 'timex2068';
        }
        // A release that named no machine of its own but wants a sound card
        // the fallback has not is Spectrum software with an extension, and the
        // Scorpion GMX is the machine here that takes both cards and runs that
        // software. Last of the machines, so a release that did name one keeps
        // it — an ATM release stays on the ATM and is fitted there instead.
        if (
            array_intersect($hardwareRequired, self::MAME_SOUND_HARDWARE)
            && $this->matchFormat($releaseFormats, 'scorpion')
        ) {
            return 'scorpion';
        }
        if ($this->matchFormat($releaseFormats, 'usp')) {
            return 'usp';
        }

        return null;
    }

    public function getRunnableTypesForEmulator(?string $emulator): array
    {
        return self::EMULATORS[$emulator]['extensions'] ?? [];
    }

    /**
     * Whether the emulator is handed the release file whole instead of one
     * file picked out of it. USP unpacks the archive itself; the Next and
     * TSConf mount every file on their SD card, because a release's data
     * files are what the launched program loads at runtime.
     */
    public function servesWholeArchive(?string $emulator): bool
    {
        return in_array($emulator, ['usp', 'zxnext', 'tsconf'], true);
    }

    /**
     * A release whose only sound is one the chosen emulator cannot produce
     * would run mute, which is not worth offering. Any other sound hardware in
     * the set is a way for it to be heard, so General Sound alongside an AY
     * only costs the GS track and the release stays playable.
     *
     * Only asked of an emulator without a NeoGS — see
     * {@see GENERAL_SOUND_EMULATORS}.
     *
     * @param string[] $hardwareRequired
     */
    private function isSilencedByUnsupportedHardware(array $hardwareRequired): bool
    {
        if (!array_intersect($hardwareRequired, self::UNSUPPORTED_HARDWARE)) {
            return false;
        }

        foreach ($hardwareRequired as $code) {
            if (in_array($code, self::UNSUPPORTED_HARDWARE, true)) {
                continue;
            }
            if ($this->catalogService->getCategoryOf($code) === HardwareGroup::SOUND) {
                return false;
            }
        }

        return true;
    }

    /**
     * A release that names no machine any emulator here can be has nothing to
     * run on. Any other computer in the set is one it also runs on, so a
     * Pentagon 2.666 beside a plain Pentagon costs only the 2.666 version and
     * the release stays playable — the same shape as the sound rule above.
     *
     * @param string[] $hardwareRequired
     */
    private function runsOnlyOnUnsupportedMachine(array $hardwareRequired): bool
    {
        if (!array_intersect($hardwareRequired, self::UNSUPPORTED_MACHINES)) {
            return false;
        }

        foreach ($hardwareRequired as $code) {
            if (in_array($code, self::UNSUPPORTED_MACHINES, true)) {
                continue;
            }
            if ($this->catalogService->getCategoryOf($code) === HardwareGroup::COMPUTERS) {
                return false;
            }
        }

        return true;
    }

    private function matchHardwareAndFormat(array $hardwareRequired, array $releaseFormats, string $emulator): bool
    {
        return array_intersect($hardwareRequired, self::EMULATORS[$emulator]['hardware']) &&
            array_intersect($releaseFormats, self::EMULATORS[$emulator]['extensions']);
    }

    private function matchHardware(array $hardwareRequired, string $emulator): bool
    {
        return (bool)array_intersect($hardwareRequired, self::EMULATORS[$emulator]['hardware'] ?? []);
    }

    private function matchFormat(array $releaseFormats, string $emulator): bool
    {
        return (bool)array_intersect($releaseFormats, self::EMULATORS[$emulator]['extensions'] ?? []);
    }
}
