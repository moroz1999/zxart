<?php

namespace ZxArt\FileParsing;

use Override;
use ZxFiles\Tape\Tzx;

class ZxParsingItemTzx extends ZxParsingItem
{
    #[Override] public function getType(): string
    {
        return 'tzx';
    }


    #[Override] protected function parse(): void
    {
        if ($this->items === null) {
            $this->items = [];

            $tape = new Tzx();
            $tape->setBinary($this->getContent());
            if ($files = $tape->getFiles()) {
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
