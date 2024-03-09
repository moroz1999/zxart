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

use Illuminate\Database\Connection;

class ZxParsingManager extends errorLogger
{
    const table = 'files_registry';
    protected $index = [];

    protected Connection $db;

    protected static array $textExtensions = [
        't', 'w', 'txt', 'bbs', 'me', 'nfo', 'nf0', 'diz', 'md', 'pok', 'd'
    ];
    protected static array $sourceCodeExtensions = [
        'asm', 'a80', 'a', 'bat', 'cmd'
    ];


    public function setDb(Connection $db): void
    {
        $this->db = $db;
    }

    /**
     * @psalm-return EngineFileRegistryRow[]
     */
    public function getFileStructureById(int $id): array
    {
        $query = $this->db->table(self::table)->where('elementId', '=', $id);
        $records = $query->get();
        foreach ($records as $key => $record) {
            $records[$key]['viewable'] = ($record['internalType'] !== 'binary' && $record['internalType']);
        }
        return $records;
    }

    /**
     * @param null|string $fileName
     *
     * @return ZxParsingItem[]
     *
     * @psalm-return array<ZxParsingItem>
     */
    public function updateFileStructure(int $elementId, string $path, string|null $fileName = null): array
    {
        $this->deleteFileStructure($elementId);
        if ($structure = $this->getFileStructure($path, $fileName)) {
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
     * @param int $elementId
     * @param int|null $parentId
     */
    protected function saveFileStructureLevel(array $structure, int $elementId, ?int $parentId = null): void
    {
        foreach ($structure as $item) {
            $internalType = $this->getInternalFileType($item->getItemName(), $item->getType(), $item->getSize(), $item->getContent());
            if ($internalType === 'plain_text') {
                $encoding = EncodingDetector::detectEncoding($item->getContent());
            } else {
                $encoding = 'none';
            }

            $info = [
                'type' => $item->getType(),
                'fileName' => $item->getItemName(),
                'md5' => $item->getMd5(),
                'size' => $item->getSize(),
                'internalType' => $internalType,
                'encoding' => $encoding ?: 'none',
                'elementId' => $elementId,
            ];

            if ($parentId) {
                $info['parentId'] = $parentId;
            }
            if ($newParentId = $this->db->table(self::table)
                ->where('elementId', '=', $elementId)
                ->insertGetId($info)
            ) {
                if ($subStructure = $item->getItems()) {
                    $this->saveFileStructureLevel($subStructure, $elementId, $newParentId);
                }
            }
        }
    }

    protected function getInternalFileType(string $fileName, string $extension, int $size, string $content): string
    {
        if ($extension === 'file') {
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        }
        if (in_array($extension, self::$textExtensions)) {
            $content = EncodingDetector::decodeText($content);
            if (!$content) {
                return 'binary';
            }
            return 'plain_text';
        } elseif (in_array($extension, self::$sourceCodeExtensions)) {
            return 'source_code';
        } elseif ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'bmp') {
            return 'pc_image';
        } elseif ($extension == 'b') {
            return 'zx_basic';
        } elseif ($size === 6912) {
            return 'zx_image_standard';
        } elseif ($size === 6144) {
            return 'zx_image_monochrome';
        } elseif ($size === 18432) {
            return 'zx_image_tricolor';
        } elseif ($size === 13824) {
            return 'zx_image_gigascreen';
        } else {
            return 'binary';
        }
    }


    /**
     * @param $path
     * @param null $fileName
     * @return ZxParsingItem[]
     */
    public function getFileStructure(string $path, $fileName = null)
    {
        $structure = [];
        if (is_file($path)) {
            if ($type = $this->detectType($path, null, $fileName)) {
                if ($type == 'tap') {
                    $file = new ZxParsingItemTap($this);
                } elseif ($type == 'tzx') {
                    $file = new ZxParsingItemTzx($this);
                } elseif ($type == 'scl') {
                    $file = new ZxParsingItemScl($this);
                } elseif ($type == 'trd') {
                    $file = new ZxParsingItemTrd($this);
                } elseif ($type == 'rar') {
                    $file = new ZxParsingItemRar($this);
                } elseif ($type == 'zip') {
                    $file = new ZxParsingItemZip($this);
                } else {
                    $file = new ZxParsingItemFile($this);
                }
                $file->setPath($path);
                if ($fileName) {
                    $file->setItemName($fileName);
                }
                $this->registerFile($file);
                $file->getItems();
                $structure = [$file];
            }
        }
        return $structure;
    }

    /**
     * @param null|string $path
     * @param null|string $content
     */
    public function detectType(string|null $path = null, string|null $content = null, $fileName = null): string
    {
        if ($fileName && ($extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)))) {
            return $extension;
        } elseif ($extension = strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            return $extension;
        } elseif ($content || ($content = file_get_contents($path))) {
            if (str_starts_with($content, 'PK')) {
                return 'zip';
            }
            if (str_starts_with($content, 'Rar')) {
                return 'rar';
            }
        }
        return $extension;
    }

    /**
     * @param ZxParsingItem $file
     */
    public function registerFile($file): void
    {
        $this->index[$file->getMd5()] = $file;
    }

    public function getFileRecord(int $id)
    {
        return $this->db->table(self::table)->where('id', '=', $id)->limit(1)->first();
    }

    public function extractFile(string $path, int $id): bool|ZxParsingItem
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

        if ($chain) {
            if ($file = $this->getFileByChain($path, $chain, $fileName)) {
                return $file;
            }
        }
        return false;
    }

    /**
     * @param string $path
     * @param string[] $chain
     * @return ZxParsingItem|boolean
     */
    public function getFileByChain($path, $chain, $fileName = null)
    {
        if ($structure = $this->getFileStructure($path, $fileName)) {
            foreach ($structure as $item) {
                if ($file = $item->getFileByChain($chain)) {
                    return $file;
                }
            }
        }
        return false;
    }
}