<?php

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
                                } elseif ($type == 'rar') {
                                    $partItem = new ZxParsingItemRar($this->zxParsingManager);
                                }  elseif ($type == 'zip') {
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
