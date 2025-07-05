<?php
declare(strict_types=1);


namespace ZxArt\Releases\Services;

final class EmulatorResolverService
{
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
        'usp' => [
            'hardware' => [],
            'extensions' => ['trd', 'tap', 'z80', 'sna', 'tzx', 'scl'],
        ],
    ];

    public function resolveEmulator(array $hardwareRequired, array $releaseFormats): ?string
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
        if ($this->matchFormat($releaseFormats, 'usp')) {
            return 'usp';
        }

        return null;
    }

    public function getRunnableTypesForEmulator(?string $emulator): array
    {
        return self::EMULATORS[$emulator]['extensions'] ?? [];
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
