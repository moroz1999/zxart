<?php

class ZxParsingItemRar extends ZxParsingItem
{
    /**
     * @return string
     *
     * @psalm-return 'rar'
     */
    public function getType()
    {
        return 'rar';
    }

    /**
     * @return ZxParsingItem[]
     *
     * @psalm-return array<ZxParsingItem>
     */
    protected function parse()
    {
        if ($this->items === null) {
            $this->items = [];
            if ($this->path) {
                $rarFilePath = $this->path;
            } else {
                $fp = tmpfile();
                fwrite($fp, $this->content);
                $stream = stream_get_meta_data($fp);
                $rarFilePath = $stream['uri'];
            }
            if (class_exists('RarArchive')) {
                $rar = RarArchive::open($rarFilePath);
                foreach ($rar->getEntries() as $entry) {
                    $itemFileName = $entry->getName();
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
                                $path = ROOT_PATH . 'temporary/' . md5($entry->getName() . $rarFilePath);
                                $entry->extract(null, $path);
                                if ($content = file_get_contents($path)) {
                                    $type = $this->zxParsingManager->detectType($part, $content);
                                    if ($type == 'tap') {
                                        $partItem = new ZxParsingItemTap($this->zxParsingManager);
                                    } elseif ($type == 'tzx') {
                                        $partItem = new ZxParsingItemTzx($this->zxParsingManager);
                                    } elseif ($type == 'scl') {
                                        $partItem = new ZxParsingItemScl($this->zxParsingManager);
                                    } elseif ($type == 'trd') {
                                        $partItem = new ZxParsingItemTrd($this->zxParsingManager);
                                    } elseif ($type == 'rar') {
                                        $partItem = new ZxParsingItemRar($this->zxParsingManager);
                                    } elseif ($type == 'rar') {
                                        $partItem = new ZxParsingItemRar($this->zxParsingManager);
                                    } else {
                                        $partItem = new ZxParsingItemFile($this->zxParsingManager);
                                    }
                                    $partItem->setContent($content);
                                    $partItem->setParentMd5($this->getMd5());
                                    $partItem->setItemName($part);
                                    $partItem->getItems();
                                    $this->zxParsingManager->registerFile($partItem);
                                    unlink($path);
                                }
                            }
                            if ($partItem) {
                                $currentParent->addItem($partItem);
                            }
                        }
                        $currentParent = $partItem;
                    }
                }

                $rar->close();
                if (isset($fp)) {
                    fclose($fp);
                }
            }
        }
        return $this->items;
    }
}