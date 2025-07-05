<?php
declare(strict_types=1);

namespace ZxArt\Releases\Services;

final class ArchiveFileResolverService
{
    private const ARCHIVE_FILE_TYPES = [
        'samcoupe' => ['dsk', 'mgt'],
        'zxnext' => ['zip', 'rar', '7z', 'tap', 'trd', 'nex', 'nxs', 'bas'],
        'zx80' => ['o', 'tap', 'tzx'],
        'zx81' => ['p', 'tap', 'tzx', 'z81'],
        'tsconf' => ['spg', 'trd', 'scl'],
        'zx128' => ['tap', 'tzx', 'trd', 'scl', 'dsk', 'fdi', 'udi', 'td0', 'mdr', 'sna', 'szx', 'dck', 'z80', 'slt',],
        'elementzxmb' => ['tar'],
    ];

    public function getArchiveFileTypesForHardware(array $hardwareCodes): array
    {
        $result = [];

        foreach ($hardwareCodes as $hardware) {
            if (!empty(self::ARCHIVE_FILE_TYPES[$hardware])) {
                $result = array_merge($result, self::ARCHIVE_FILE_TYPES[$hardware]);
            }
        }

        return array_unique($result);
    }

    public function filterArchiveFiles(array $releaseStructure, array $hardwareCodes): array
    {
        $platformCodes = array_keys(self::ARCHIVE_FILE_TYPES);

        $hardwareCodes = array_map(
            static fn($code) => $code === 'zx48' ? 'zx128' : $code,
            $hardwareCodes
        );
        $zx81Codes = ['zx8116', 'zx811', 'zx812', 'zx8132', 'zx8164', 'lambda8300'];
        $hardwareCodes = array_map(
            static fn($code) => in_array($code, $zx81Codes) ? 'zx81' : $code,
            $hardwareCodes
        );

        $hardwareCodes = array_intersect($hardwareCodes, $platformCodes);

        if (empty($hardwareCodes)) {
            $hardwareCodes = ['zx128'];
        }

        $archiveTypes = $this->getArchiveFileTypesForHardware($hardwareCodes);
        $result = [];
        $skipIds = [];

        foreach ($releaseStructure as $file) {

            $ext = strtolower(pathinfo($file['fileName'], PATHINFO_EXTENSION));
            if (in_array($file['parentId'], $skipIds, true)){
                $skipIds[] = $file['id'];
                continue;
            }

            if (in_array($ext, $archiveTypes, true)) {
                $result[] = $file;
                $skipIds[] = $file['id'];
            }
        }

        return $result;
    }

}
