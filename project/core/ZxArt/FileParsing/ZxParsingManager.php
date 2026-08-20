<?php


namespace ZxArt\FileParsing;

use EncodingDetector;
use errorLogger;
use Illuminate\Database\Connection;
use ZxFiles\ContainerFormat;
use ZxFiles\Detection\ContainerReader;

/**
 * @psalm-type EngineFileRegistryRow = array{
 *     id: int,
 *     md5: string,
 *     parentId: int,
 *     fileName: string,
 *     size: int,
 *     elementId: int,
 *     type: 'folder'|'file'|'zip'|'7z'|'rar'|'trd'|'scl'|'tap'|'tzx'|'dsk'|'fdi'|'udi'|'opd'|'tar',
 *     encoding: 'UTF-8'|'Windows-1251'|'ISO-8859-1'|'IBM866'|'CP866'|'KOI8-R'|'Windows-1252'|'none',
 *     internalType: 'plain_text'|'source_code'|'pc_image'|'zx_basic'|'zx_image_standard'|'zx_image_monochrome'|'zx_image_tricolor'|'zx_image_gigascreen'|'binary'
 * }
 */
final class ZxParsingManager extends errorLogger
{
    const string table = 'files_registry';

    private static array $textExtensions = [
        't', 'w', 'txt', 'bbs', 'me', 'nfo', 'nf0', 'diz', 'md', 'pok', 'd'
    ];
    private static array $sourceCodeExtensions = [
        'asm', 'a80', 'a', 'bat', 'cmd'
    ];

    private readonly ContainerReader $containerReader;

    public function __construct(
        private readonly Connection $db
    )
    {
        $this->containerReader = new ContainerReader();
    }

    public function getContainerReader(): ContainerReader
    {
        return $this->containerReader;
    }

    /**
     * The parsing item a registry type calls for. Every container format zx-files reads is
     * one item class, so a new format needs nothing here.
     */
    public function createItem(string $type): ZxParsingItem
    {
        if ($format = ContainerFormat::fromExtension($type)) {
            return new ZxParsingItemContainer($this, $format);
        }

        return match ($type) {
            'zip' => new ZxParsingItemZip($this),
            'rar' => new ZxParsingItemRar($this),
            'folder' => new ZxParsingItemFolder($this),
            default => new ZxParsingItemFile($this),
        };
    }


    /**
     * @psalm-return EngineFileRegistryRow[]
     */
    public function getStructureRecordsById(int $elementId): array
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
        $records = $this->getStructureRecordsById($elementId);
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

        // 'b' is the TR-DOS and tape extension, 'bas' the one +3DOS and esxDOS use.
        if ($extension === 'b' || $extension === 'bas') {
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
            $file = $this->createItem($type);
            $file->setPath($path);
            if ($fileName) {
                $file->setItemName($fileName);
            }
            $file->getItems();
            $structure = [$file];
        }
        return $structure;
    }

    /**
     * The extension a file goes by, which is what decides how it is parsed. Archives without
     * one are recognised by their signature instead.
     */
    public function detectType(string|null $path = null, string|null $content = null, string|null $fileName = null): string
    {
        foreach ([$fileName, $path] as $name) {
            if ($name !== null && ($extension = strtolower(pathinfo($name, PATHINFO_EXTENSION))) !== '') {
                return $extension;
            }
        }

        if ($content === null && $path !== null && is_file($path)) {
            $read = file_get_contents($path);
            $content = $read === false ? null : $read;
        }
        if ($content === null) {
            return '';
        }

        if (str_starts_with($content, 'PK')) {
            return 'zip';
        }
        if (str_starts_with($content, 'Rar')) {
            return 'rar';
        }

        return '';
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

    /**
     * @psalm-return ZxParsingItem[]
     */
    public function getFileStructure(int $elementId): array
    {
        $rows = $this->getStructureRecordsById($elementId);

        if ($rows === []) {
            return [];
        }

        // index by parentId
        $grouped = [];
        foreach ($rows as $row) {
            $parentId = $row['parentId'] ?? 0;
            $grouped[$parentId][] = $row;
        }

        return $this->buildItemsFromRows($grouped, 0);
    }

    /**
     * @psalm-return ZxParsingItem[]
     */
    private function buildItemsFromRows(array $grouped, int $parentId): array
    {
        $items = [];

        foreach ($grouped[$parentId] ?? [] as $row) {
            $item = $this->rowToItem($row);

            if (!empty($grouped[$row['id']] ?? [])) {
                $children = $this->buildItemsFromRows($grouped, $row['id']);
                foreach ($children as $child) {
                    $item->addItem($child);
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    private function rowToItem(array $row): ZxParsingItem
    {
        $item = $this->createItem((string)$row['type']);

        $item->setItemName($row['fileName']);
        $item->setContent('');
        $item->setParentMd5($row['parentId'] ? (string)$row['parentId'] : '');
        $item->setMd5($row['md5']);
        $item->setPath('');

        return $item;
    }

}