<?php
declare(strict_types=1);


namespace ZxArt\Releases\Services;

use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Hardware\HardwareGroup;

final class EmulatorResolverService
{
    /**
     * Hardware the online emulators cannot emulate (General Sound). It is a sound
     * extension, so it only decides playability when it is the only sound there
     * is — see {@see isSilencedByUnsupportedHardware()}.
     */
    private const array UNSUPPORTED_HARDWARE = ['gs'];

    /**
     * Machines none of the cores here can be. Without this a release needing one
     * falls through to the Spectrum fallback on its format alone and is offered
     * as a machine it is not — the ATM Turbo, and BaseConf as the ZX Evolution
     * configuration of that family, all have memory and video of their own that
     * nothing here emulates.
     *
     * Like General Sound they only decide playability when no other machine is
     * in the set — see {@see runsOnlyOnUnsupportedMachine()}.
     */
    private const array UNSUPPORTED_MACHINES = ['atm', 'atm2', 'baseconf'];

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
        if ($this->isSilencedByUnsupportedHardware($hardwareRequired)) {
            return null;
        }
        if ($this->runsOnlyOnUnsupportedMachine($hardwareRequired)) {
            return null;
        }
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
     * A release whose only sound is one the emulators cannot produce would run
     * mute, which is not worth offering. Any other sound hardware in the set is a
     * way for it to be heard, so General Sound alongside an AY only costs the GS
     * track and the release stays playable.
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
     * run on. Any other computer in the set is one it also runs on, so an ATM
     * beside a Spectrum costs only the ATM version and the release stays
     * playable — the same shape as the General Sound rule above.
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
