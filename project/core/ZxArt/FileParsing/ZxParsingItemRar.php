<?php

namespace ZxArt\FileParsing;
use Override;

class ZxParsingItemRar extends ZxParsingItem
{
    #[Override] public function getType(): string
    {
        return 'rar';
    }

    #[Override] protected function parse(): void
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
                                $path = ROOT_PATH . 'temporary/' . md5($entry->getName() . $rarFilePath);
                                $entry->extract(null, $path);
                                if ($content = file_get_contents($path)) {
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
                                    } else {
                                        $partItem = new ZxParsingItemFile($this->zxParsingManager);
                                    }
                                    $partItem->setContent($content);
                                    $partItem->setParentMd5($this->getMd5());
                                    $partItem->setItemName($part);
                                    $partItem->getItems();
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
    }
}