<?php
declare(strict_types=1);

namespace ZxArt\Releases\Services;

final class ArchiveFileResolverService
{
    private const array ARCHIVE_FILE_TYPES = [
        'samcoupe' => ['dsk', 'mgt', 'sad', 'cpm', 'tap', 'tzx'],
        'esxdos' => ['tar', 'zip', 'rar', '7z'],
        'zxnext' => ['tar', 'zip', 'rar', '7z', 'tap', 'trd', 'nex', 'nxs', 'bas', 'snx'],
        'zx80' => ['o', 'tap', 'tzx'],
        'zx81' => ['p', 'tap', 'tzx', 'z81'],
        'tsconf' => ['tar', 'zip', 'rar', '7z', 'spg', 'trd', 'scl'],
        'zx128' => ['tap', 'tzx', 'trd', 'scl', 'dsk', 'fdi', 'udi', 'td0', 'mdr', 'sna', 'szx', 'dck', 'z80',
            'slt', '$c', '$b', 'd40', 'd80', 'opd', 'mgt', 'mbd', 'rom', 'mld', 'bin', 'tar', 'iso'],
        'elementzxmb' => ['tar', 'zip', 'rar', '7z', 'img', 'bin', 'tap'],
        'zxuno' => ['tar', 'zip', 'rar', '7z', 'img', 'bin', 'tap'],
        'sinclairql' => ['tar', 'zip', 'rar', '7z', 'img', 'bin', 'tap'],
    ];

    private const array ZX81_CODES = ['zx8116', 'zx811', 'zx812', 'zx8132', 'zx8164', 'lambda8300'];
    private const array TOP_LEVEL_PARENT_VALUES = [null, 0];
    private const array HOBETA_FILE_TYPES = ['$c', '$b'];

    /** @var string[] */
    private const array CONTAINER_FILE_TYPES = ['zip', 'rar', '7z', 'tar'];

    private function getArchiveFileTypesForHardware(array $hardwareCodes): array
    {
        $result = [];

        foreach ($hardwareCodes as $hardware) {
            if (!empty(self::ARCHIVE_FILE_TYPES[$hardware])) {
                $result = array_merge($result, self::ARCHIVE_FILE_TYPES[$hardware]);
            }
        }

        return array_values(array_unique($result));
    }

    private function findHobeta(array $releaseStructure): array
    {
        $hobeta = [];
        foreach ($releaseStructure as $file) {
            $ext = strtolower(pathinfo($file['fileName'] ?? '', PATHINFO_EXTENSION));

            if (!in_array($ext, self::HOBETA_FILE_TYPES, true)) {
                continue;
            }

            $hobeta[] = $file;
        }
        return $hobeta;
    }

    public function filterArchiveFiles(array $releaseStructure, array $hardwareCodes): array
    {
        $platforms = array_keys(self::ARCHIVE_FILE_TYPES);

        $normalized = $this->getNormalizedHardware($hardwareCodes, $platforms);

        $hobetaFiles = $this->findHobeta($releaseStructure);

        $fileTypes = $this->getArchiveFileTypesForHardware($normalized);
        $topLevelOnly = $this->requiresTopLevelOnly($normalized);

        $filtered = [];
        foreach ($releaseStructure as $file) {
            $ext = strtolower((string)pathinfo($file['fileName'] ?? '', PATHINFO_EXTENSION));

            if (!in_array($ext, $fileTypes, true)) {
                continue;
            }

            if ($topLevelOnly && !$this->isTopLevel($file)) {
                continue;
            }

            $filtered[] = $file;
        }
        if ($hobetaFiles !== []) {
            // Other release types among filtered (exclude hobeta)
            $otherReleases = array_values(array_filter(
                $filtered,
                static fn(array $f): bool => !in_array(
                    strtolower((string)pathinfo($f['fileName'] ?? '', PATHINFO_EXTENSION)),
                    self::HOBETA_FILE_TYPES,
                    true
                )
            ));

            // Case 1: there are other release types => return other releases + ALL hobeta
            if ($otherReleases !== []) {
                return array_merge($otherReleases, $hobetaFiles);
            }

            // Case 2: only hobeta present => return the single top-level container
            $container = $this->findTopLevelContainer($releaseStructure);
            return $container !== null ? [$container] : $hobetaFiles;
        }

        // No hobeta in archive — just return the filtered set
        return $filtered;
    }

    private function isTopLevel(array $file): bool
    {
        $parentId = $file['parentId'] ?? null;
        return in_array($parentId, self::TOP_LEVEL_PARENT_VALUES, true);
    }

    private function requiresTopLevelOnly(array $hardwareCodes): bool
    {
        return (bool)array_intersect($hardwareCodes, ['tsconf', 'zxnext', 'esxdos', 'zxuno', 'elementzxmb', 'sinclairql']);
    }

    /**
     * @return string[]
     */
    private function getNormalizedHardware(array $hardwareCodes, array $platformCodes): array
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

    /**
     * Return the single top-level container file if present (parentId is null or 0).
     * Falls back to null when no container exists.
     *
     * @param array<int,array<string,mixed>> $releaseStructure
     * @return array<string,mixed>|null
     */
    private function findTopLevelContainer(array $releaseStructure): ?array
    {
        foreach ($releaseStructure as $file) {
            if (!$this->isTopLevel($file)) {
                continue;
            }
            $ext = strtolower((string)pathinfo($file['fileName'] ?? '', PATHINFO_EXTENSION));
            if (in_array($ext, self::CONTAINER_FILE_TYPES, true)) {
                return $file;
            }
        }
        return null;
    }
}
