<?php

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
        if (!$this->content && $this->path && is_file($this->path)) {
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
