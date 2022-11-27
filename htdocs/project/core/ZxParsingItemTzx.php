<?php

class ZxParsingItemTzx extends ZxParsingItem
{
    public function getType()
    {
        return 'tzx';
    }

    protected function parse()
    {
        if ($this->items === null) {
            $this->items = [];

            $tape = new \ZxFiles\Tape\Tzx();
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
