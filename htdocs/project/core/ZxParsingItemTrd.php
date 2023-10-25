<?php

class ZxParsingItemTrd extends ZxParsingItem
{
    public function getType()
    {
        return 'trd';
    }

    protected function parse()
    {
        $this->items = [];

        $disk = new \ZxFiles\Disk\Trd();
        $disk->setBinary($this->getContent());
        if ($files = $disk->getFiles()) {
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
