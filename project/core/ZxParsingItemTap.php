<?php

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
            if ($files = $tape->getFiles()) {
                foreach ($files as $file) {
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
}
