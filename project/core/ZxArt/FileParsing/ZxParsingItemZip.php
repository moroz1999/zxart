<?php

namespace ZxArt\FileParsing;

class ZxParsingItemZip extends ZxParsingItem
{

    #[Override] public function getType(): string
    {
        return 'zip';
    }

    #[Override] protected function parse(): void
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
            if ($zip->open($zipFilePath) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $itemFileName = $zip->getNameIndex($i);
                    if ($isDir = (str_ends_with($itemFileName, '/'))) {
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
                            } else {
                                if ($content = $zip->getFromIndex($i)) {
                                    $type = $this->zxParsingManager->detectType($part, $content);
                                    if ($type === 'tap') {
                                        $partItem = new ZxParsingItemTap($this->zxParsingManager);
                                    } elseif ($type === 'tzx') {
                                        $partItem = new ZxParsingItemTzx($this->zxParsingManager);
                                    } elseif ($type === 'scl') {
                                        $partItem = new ZxParsingItemScl($this->zxParsingManager);
                                    } elseif ($type === 'trd') {
                                        $partItem = new ZxParsingItemTrd($this->zxParsingManager);
                                    } elseif ($type === 'rar') {
                                        $partItem = new ZxParsingItemRar($this->zxParsingManager);
                                    } elseif ($type === 'zip') {
                                        $partItem = new ZxParsingItemZip($this->zxParsingManager);
                                    } else {
                                        $partItem = new ZxParsingItemFile($this->zxParsingManager);
                                    }
                                    $partItem->setContent($content);
                                    $partItem->setParentMd5($this->getMd5());
                                    $partItem->setItemName($part);
                                    $partItem->getItems();
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
            }
            if (isset($fp)) {
                fclose($fp);
            }
        }
    }
}
