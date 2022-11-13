<?php

use Illuminate\Database\Connection;

class ZxParsingManager extends errorLogger
{
    const table = 'files_registry';
    protected $index = [];

    protected Connection $db;

    public function setDb(Connection $db)
    {
        $this->db = $db;
    }

    public function getFileStructureById($id)
    {
        $query = $this->db->table(self::table)->where('elementId', '=', $id);
        return $query->get();
    }

    public function saveFileStructure($elementId, $path, $fileName = null)
    {
        $this->deleteFileStructure($elementId);
        if ($structure = $this->getFileStructure($path, $fileName)) {
            $this->saveFileStructureLevel($structure, $elementId);
        }
        return $structure;
    }

    public function deleteFileStructure($elementId)
    {
        $this->db->table(self::table)->where('elementId', '=', $elementId)->delete();
    }

    /**
     * @param ZxParsingItem[] $structure
     * @param $elementId
     * @param null $parentId
     */
    protected function saveFileStructureLevel($structure, $elementId, $parentId = null)
    {
        foreach ($structure as $item) {
            $info = [
                'type' => $item->getType(),
                'fileName' => $item->getItemName(),
                'md5' => $item->getMd5(),
                'size' => $item->getSize(),
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

    /**
     * @param $path
     * @param null $fileName
     * @return ZxParsingItem[]
     */
    public function getFileStructure($path, $fileName = null)
    {
        $structure = [];
        if (is_file($path)) {
            if ($type = $this->detectType($path, null, $fileName)) {
                if ($type == 'tap') {
                    $file = new ZxParsingItemTap($this);
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

    public function detectType($path = null, $content = null, $fileName = null)
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
    public function registerFile($file)
    {
        $this->index[$file->getMd5()] = $file;
    }


    public function extractFile($path, $id)
    {
        $chain = [];
        $fileName = false;
        do {
            if ($record = $this->db->table(self::table)->where('id', '=', $id)->limit(1)->first(
                [
                    'fileName',
                    'parentId',
                    'md5',
                ]
            )
            ) {
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