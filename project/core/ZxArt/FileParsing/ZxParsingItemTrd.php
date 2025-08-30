<?php

namespace ZxArt\FileParsing;

use ZxFiles\Disk\Trd;

class ZxParsingItemTrd extends ZxParsingItem
{

    public function getType(): string
    {
        return 'trd';
    }


    protected function parse(): void
    {
        $this->items = [];

        $disk = new Trd();
        $disk->setBinary($this->getContent());
        if ($files = $disk->getFiles()) {
            foreach ($files as $file) {
                $item = new ZxParsingItemFile($this->zxParsingManager);

                $item->setContent($file->getContents());
                $item->setParentMd5($this->getMd5());
                $item->setItemName($file->getFullName());

                $this->items[] = $item;
            }
        }

    }
}
