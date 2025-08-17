<?php
declare(strict_types=1);

namespace ZxArt\Releases\Services;

final class ArchiveFileResolverService
{
    private const ARCHIVE_FILE_TYPES = [
        'samcoupe'   => ['dsk', 'mgt'],
        'esxdos'     => ['zip', 'rar', '7z'],
        'zxnext'     => ['zip', 'rar', '7z', 'tap', 'trd', 'nex', 'nxs', 'bas'],
        'zx80'       => ['o', 'tap', 'tzx'],
        'zx81'       => ['p', 'tap', 'tzx', 'z81'],
        'tsconf'     => ['spg', 'trd', 'scl'],
        'zx128'      => ['tap', 'tzx', 'trd', 'scl', 'dsk', 'fdi', 'udi', 'td0', 'mdr', 'sna', 'szx', 'dck', 'z80', 'slt', '$b'],
        'elementzxmb'=> ['tar'],
    ];

    private const ZX81_CODES = ['zx8116', 'zx811', 'zx812', 'zx8132', 'zx8164', 'lambda8300'];
    private const TOP_LEVEL_PARENT_VALUES = [null, 0];

    public function getArchiveFileTypesForHardware(array $hardwareCodes): array
    {
        $result = [];

        foreach ($hardwareCodes as $hardware) {
            if (!empty(self::ARCHIVE_FILE_TYPES[$hardware])) {
                $result = array_merge($result, self::ARCHIVE_FILE_TYPES[$hardware]);
            }
        }

        return array_values(array_unique($result));
    }

    public function filterArchiveFiles(array $releaseStructure, array $hardwareCodes): array
    {
        $platforms = array_keys(self::ARCHIVE_FILE_TYPES);

        $normalized = $this->getNormalizedHardware($hardwareCodes, $platforms);

        $archiveTypes = $this->getArchiveFileTypesForHardware($normalized);
        $topLevelOnly = $this->requiresTopLevelOnly($normalized);

        $result  = [];
        $skipIds = [];

        foreach ($releaseStructure as $file) {
            $ext = strtolower((string) pathinfo($file['fileName'] ?? '', PATHINFO_EXTENSION));

            if (in_array($file['parentId'] ?? null, $skipIds, true)) {
                $skipIds[] = $file['id'] ?? null;
                continue;
            }

            if (!in_array($ext, $archiveTypes, true)) {
                continue;
            }

            if ($topLevelOnly && !$this->isTopLevel($file)) {
                continue;
            }

            $result[]  = $file;
            $skipIds[] = $file['id'] ?? null;
        }

        return $result;
    }

    private function isTopLevel(array $file): bool
    {
        $parentId = $file['parentId'] ?? null;
        return in_array($parentId, self::TOP_LEVEL_PARENT_VALUES, true);
    }

    private function requiresTopLevelOnly(array $hardwareCodes): bool
    {
        return (bool) array_intersect($hardwareCodes, ['zxnext', 'esxdos']);
    }

    /**
     * @return string[]
     */
    public function getNormalizedHardware(array $hardwareCodes, array $platformCodes): array
    {
        $normalized = array_map(
            static fn(string $code): string => $code === 'zx48' ? 'zx128' : $code,
            $hardwareCodes
        );

        $normalized = array_map(
            static fn(string $code): string => in_array($code, self::ZX81_CODES, true) ? 'zx81' : $code,
            $normalized
        );

        $normalized = array_values(array_intersect($normalized, $platformCodes));

        if ($normalized === []) {
            $normalized = ['zx128'];
        }
        return $normalized;
    }
}
