<?php

namespace ZxArt\FileParsing;

use Override;
use ZxFiles\Disk\Scl;

class ZxParsingItemScl extends ZxParsingItem
{
    #[Override] public function getType(): string
    {
        return 'scl';
    }

    #[Override] protected function parse(): void
    {
        if ($this->items === null) {
            $this->items = [];

            $disk = new Scl();
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
}
