<?php
/**
 * @psalm-type EngineFileRegistryRow = array{
 *     id: int,
 *     md5: string,
 *     parentId: int,
 *     fileName: string,
 *     size: int,
 *     elementId: int,
 *     type: 'folder'|'trd'|'tap'|'scl'|'file'|'zip'|'7z'|'rar'|'tar'|'tzx',
 *     encoding: 'UTF-8'|'Windows-1251'|'ISO-8859-1'|'IBM866'|'CP866'|'KOI8-R'|'Windows-1252'|'none',
 *     internalType: 'plain_text'|'source_code'|'pc_image'|'zx_basic'|'zx_image_standard'|'zx_image_monochrome'|'zx_image_tricolor'|'zx_image_gigascreen'|'binary'
 * }
 */

namespace ZxArt\FileParsing;

use EncodingDetector;
use EngineFileRegistryRow;
use errorLogger;
use Illuminate\Database\Connection;

final class ZxParsingManager extends errorLogger
{
    const string table = 'files_registry';

    private static array $textExtensions = [
        't', 'w', 'txt', 'bbs', 'me', 'nfo', 'nf0', 'diz', 'md', 'pok', 'd'
    ];
    private static array $sourceCodeExtensions = [
        'asm', 'a80', 'a', 'bat', 'cmd'
    ];

    public function __construct(
        private readonly Connection $db
    )
    {

    }


    /**
     * @psalm-return EngineFileRegistryRow[]
     */
    public function getFileStructureById(int $elementId): array
    {
        $query = $this->db->table(self::table)->where('elementId', '=', $elementId);
        $records = $query->get();
        foreach ($records as $key => $record) {
            $records[$key]['viewable'] = ($record['internalType'] !== 'binary' && $record['internalType']);
            if ($record['internalType'] === 'plain_text' && $record['encoding'] === 'none') {
                $records[$key]['viewable'] = false;
            }
        }
        return $records;
    }

    public function getTopFileRecord(int $elementId): array|null
    {
        $records = $this->getFileStructureById($elementId);
        if ($records !== []) {
            $record = array_find($records, static fn(array $record): bool => $record['parentId'] === 0);
            return $record;
        }
        return null;
    }

    /**
     *
     * @return ZxParsingItem[]
     *
     * @psalm-return array<ZxParsingItem>
     */
    public function updateFileStructure(int $elementId, string $path, string|null $fileName = null): array
    {
        $this->deleteFileStructure($elementId);
        if ($structure = $this->parseFileStructure($path, $fileName)) {
            $this->saveFileStructureLevel($structure, $elementId);
        }
        return $structure;
    }

    public function deleteFileStructure(int $elementId): void
    {
        $this->db->table(self::table)->where('elementId', '=', $elementId)->delete();
    }

    /**
     * @param ZxParsingItem[] $structure
     */
    private function saveFileStructureLevel(array $structure, int $elementId, ?int $parentId = null): void
    {
        foreach ($structure as $item) {
            $internalType = $this->getInternalFileType($item->getItemName(), $item->getType(), $item->getSize(), $item->getContent());
            if ($internalType === 'plain_text') {
                $encoding = EncodingDetector::detectEncoding($item->getContent());
            } else {
                $encoding = 'none';
            }

            $data = [
                'type' => $item->getType(),
                'fileName' => $item->getItemName(),
                'md5' => $item->getMd5(),
                'size' => $item->getSize(),
                'internalType' => $internalType,
                'encoding' => $encoding ?: 'none',
                'elementId' => $elementId,
            ];

            if ($parentId) {
                $data['parentId'] = $parentId;
            }
            $newParentId = $this->db->table(self::table)
                ->where('elementId', '=', $elementId)
                ->insertGetId($data);
            if ($newParentId && $subStructure = $item->getItems()) {
                $this->saveFileStructureLevel($subStructure, $elementId, $newParentId);
            }
        }
    }

    private function getInternalFileType(string $fileName, string $extension, int $size, string $content): string
    {
        if ($extension === 'file') {
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        }
        if (in_array($extension, self::$textExtensions, true)) {
            $decoded = EncodingDetector::decodeText($content);
            if (!$decoded) {
                return 'binary';
            }
            return 'plain_text';
        }

        if (in_array($extension, self::$sourceCodeExtensions, true)) {
            return 'source_code';
        }

        if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'bmp') {
            return 'pc_image';
        }

        if ($extension === 'b') {
            return 'zx_basic';
        }

        if ($size === 6912) {
            return 'zx_image_standard';
        }

        if ($size === 6144) {
            return 'zx_image_monochrome';
        }

        if ($size === 18432) {
            return 'zx_image_tricolor';
        }

        if ($size === 13824) {
            return 'zx_image_gigascreen';
        }

        return 'binary';
    }


    /**
     * @param null $fileName
     * @return ZxParsingItem[]
     */
    public function parseFileStructure(string $path, $fileName = null): array
    {
        $structure = [];
        if (is_file($path) && ($type = $this->detectType($path, null, $fileName))) {
            if ($type === 'tap') {
                $file = new ZxParsingItemTap($this);
            } elseif ($type === 'tzx') {
                $file = new ZxParsingItemTzx($this);
            } elseif ($type === 'scl') {
                $file = new ZxParsingItemScl($this);
            } elseif ($type === 'trd') {
                $file = new ZxParsingItemTrd($this);
            } elseif ($type === 'rar') {
                $file = new ZxParsingItemRar($this);
            } elseif ($type === 'zip') {
                $file = new ZxParsingItemZip($this);
            } else {
                $file = new ZxParsingItemFile($this);
            }
            $file->setPath($path);
            if ($fileName) {
                $file->setItemName($fileName);
            }
            $file->getItems();
            $structure = [$file];
        }
        return $structure;
    }

    public function detectType(string|null $path = null, string|null $content = null, $fileName = null): string
    {
        if ($fileName && ($extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)))) {
            return $extension;
        }

        if (!($extension = strtolower(pathinfo($path, PATHINFO_EXTENSION)))) {
            if ($content || ($content = file_get_contents($path))) {
                if (str_starts_with($content, 'PK')) {
                    return 'zip';
                }
                if (str_starts_with($content, 'Rar')) {
                    return 'rar';
                }
            }
        }
        return $extension;
    }

    public function getFileRecord(int $id)
    {
        return $this->db->table(self::table)->where('id', '=', $id)->limit(1)->first();
    }

    public function extractFile(string $path, int $id): ?ZxParsingItem
    {
        $chain = [];
        $fileName = false;
        do {
            /**
             * @var EngineFileRegistryRow|null $record
             */
            if ($record = $this->getFileRecord($id)) {
                $fileName = $record['fileName'];

                $chain[] = $record['md5'];
                $id = $record['parentId'];
            }
        } while ($record && $record['parentId']);

        if ($chain && $file = $this->getFileByChain($path, $chain, $fileName)) {
            return $file;
        }
        return null;
    }

    /**
     * @param string[] $chain
     * @param null $fileName
     */
    public function getFileByChain(string $path, array $chain, $fileName = null): ZxParsingItem|bool
    {
        if ($structure = $this->parseFileStructure($path, $fileName)) {
            foreach ($structure as $item) {
                if ($file = $item->getFileByChain($chain)) {
                    return $file;
                }
            }
        }
        return false;
    }
}