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
    private const UNSUPPORTED_HARDWARE = ['gs'];

    /** Snapshots and tapes JSSpeccy loads; it has no cartridge (dck) support. */
    private const JSSPECCY_EXTENSIONS = ['tap', 'tzx', 'z80', 'sna', 'szx'];

    private const EMULATORS = [
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
        'zxnext' => [
            'hardware' => ['zxnext'],
            'extensions' => ['zip', 'nex'],
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
//        if ($this->matchHardwareAndFormat($hardwareRequired, $releaseFormats, 'zxnext')) {
//            return 'zxnext';
//        }
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
