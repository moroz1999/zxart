<?php

use Illuminate\Database\Connection;

class ZxParsingManager
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
                $this->saveFileStructureLevel($item->getItems(), $elementId, $newParentId);
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
            if (substr($content, 0, 2) == 'PK') {
                return 'zip';
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

abstract class ZxParsingItem
{
    protected $path;
    protected $itemName;
    protected $content;
    /**
     * @var ZxParsingItem[]
     */
    protected $items;
    protected $parentMd5;
    protected $md5;
    protected $zxParsingManager;

    public function __construct(ZxParsingManager $zxParsingManager)
    {
        $this->zxParsingManager = $zxParsingManager;
    }

    /**
     * @return mixed
     */
    public function getParentMd5()
    {
        return $this->parentMd5;
    }

    /**
     * @param mixed $parentMd5
     */
    public function setParentMd5($parentMd5)
    {
        $this->parentMd5 = $parentMd5;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        if (!$this->content && is_file($this->path)) {
            $this->content = file_get_contents($this->path);
        }
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return ZxParsingItem[]
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->parse();
        }
        return $this->items;
    }

    public function getMd5()
    {
        if (!$this->md5) {
            $this->md5 = md5($this->getContent());
        }
        return $this->md5;
    }

    public function getSize()
    {
        return strlen($this->getContent());
    }

    /**
     * @return mixed
     */
    public function getItemName()
    {
        return $this->itemName;
    }

    public function getItemExtension()
    {
        if ($itemName = $this->getItemName()) {
            return strtolower(pathinfo($itemName, PATHINFO_EXTENSION));
        }
        return '';
    }

    /**
     * @param mixed $itemName
     */
    public function setItemName($itemName)
    {
        $this->itemName = $itemName;
    }

    public function getItemByName($itemName)
    {
        if ($items = $this->getItems()) {
            foreach ($items as $item) {
                if ($item->getItemName() == $itemName) {
                    return $item;
                }
            }
        }
        return false;
    }

    public function addItem($item)
    {
        $this->items[] = $item;
    }

    abstract public function getType();

    abstract protected function parse();

    public function getFileByChain($chain)
    {
        if ($md5 = array_pop($chain)) {
            if ($this->getMd5() == $md5) {
                if ($chain) {
                    foreach ($this->getItems() as $item) {
                        if ($file = $item->getFileByChain($chain)) {
                            return $file;
                        }
                    }
                } else {
                    return $this;
                }
            }
        }
        return false;
    }
}

class ZxParsingItemZip extends ZxParsingItem
{
    public function getType()
    {
        return 'zip';
    }

    protected function parse()
    {
        if ($this->items === null) {
            $this->items = [];
            if ($this->path) {
                $zipFilePath = $this->path;
            } else {
                $fp = tmpfile();
                fwrite($fp, $this->content);
                $stream = stream_get_meta_data($fp);
                $zipFilePath = $stream['uri'];
            }
            $zip = new ZipArchive();
            $zip->open($zipFilePath);

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $itemFileName = $zip->getNameIndex($i);
                if ($isDir = (substr($itemFileName, -1, 1) == '/')) {
                    $itemFileName = substr($itemFileName, 0, -1);
                }
                $parts = explode('/', $itemFileName);

                $currentParent = $this;
                while ($part = array_shift($parts)) {
                    if (!($partItem = $currentParent->getItemByName($part))) {
                        if ($parts || $isDir) {
                            $partItem = new ZxParsingItemFolder($this->zxParsingManager);
                            $partItem->setParentMd5($this->getMd5());
                            $partItem->setItemName($part);
                            $this->zxParsingManager->registerFile($partItem);
                        } else {
                            if ($content = $zip->getFromIndex($i)) {
                                $type = $this->zxParsingManager->detectType($part, $content);
                                if ($type == 'tap') {
                                    $partItem = new ZxParsingItemTap($this->zxParsingManager);
                                } elseif ($type == 'scl') {
                                    $partItem = new ZxParsingItemScl($this->zxParsingManager);
                                } elseif ($type == 'trd') {
                                    $partItem = new ZxParsingItemTrd($this->zxParsingManager);
                                } elseif ($type == 'zip') {
                                    $partItem = new ZxParsingItemZip($this->zxParsingManager);
                                } else {
                                    $partItem = new ZxParsingItemFile($this->zxParsingManager);
                                }
                                $partItem->setContent($content);
                                $partItem->setParentMd5($this->getMd5());
                                $partItem->setItemName($part);
                                $partItem->getItems();
                                $this->zxParsingManager->registerFile($partItem);
                            }
                        }
                        if ($partItem) {
                            $currentParent->addItem($partItem);
                        }
                    }
                    $currentParent = $partItem;
                }
            }

            $zip->close();
            if (isset($fp)) {
                fclose($fp);
            }
        }
        return $this->items;
    }
}

class ZxParsingItemFile extends ZxParsingItem
{
    public function getType()
    {
        return 'file';
    }

    protected function parse()
    {
    }
}

class ZxParsingItemScl extends ZxParsingItem
{
    public function getType()
    {
        return 'scl';
    }

    protected function parse()
    {
        if ($this->items === null) {
            $this->items = [];

            $disk = new \ZxFiles\Disk\Scl();
            $disk->setBinary($this->getContent());
            foreach ($disk->getFiles() as $file) {
                $item = new ZxParsingItemFile($this->zxParsingManager);

                $item->setContent($file->getContents());
                $item->setParentMd5($this->getMd5());
                $item->setItemName($file->getFullName());
                $this->zxParsingManager->registerFile($item);

                $this->items[] = $item;
            }
        }
    }
}

class ZxParsingItemTap extends ZxParsingItem
{
    public function getType()
    {
        return 'tap';
    }

    protected function parse()
    {
        if ($this->items === null) {
            $this->items = [];

            $tape = new \ZxFiles\Tape\Tap();
            $tape->setBinary($this->getContent());
            foreach ($tape->getFiles() as $file) {
                $item = new ZxParsingItemFile($this->zxParsingManager);

                $item->setContent($file->getContents());
                $item->setParentMd5($this->getMd5());
                $item->setItemName($file->getFullName());
                $this->zxParsingManager->registerFile($item);

                $this->items[] = $item;
            }
        }
    }
}

class ZxParsingItemTrd extends ZxParsingItem
{
    public function getType()
    {
        return 'trd';
    }

    protected function parse()
    {
        $this->items = [];

        $disk = new \ZxFiles\Disk\Trd();
        $disk->setBinary($this->getContent());
        foreach ($disk->getFiles() as $file) {
            $item = new ZxParsingItemFile($this->zxParsingManager);

            $item->setContent($file->getContents());
            $item->setParentMd5($this->getMd5());
            $item->setItemName($file->getFullName());
            $this->zxParsingManager->registerFile($item);

            $this->items[] = $item;
        }
    }
}

class ZxParsingItemFolder extends ZxParsingItem
{
    public function getType()
    {
        return 'folder';
    }

    protected function parse()
    {
    }

    public function getContent()
    {
        if (!$this->content) {
            $this->content = $this->parentMd5 . '/' . $this->itemName;
        }
        return $this->content;
    }
}
