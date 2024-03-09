<?php

class ZxParsingItemScl extends ZxParsingItem
{
    /**
     * @return string
     *
     * @psalm-return 'scl'
     */
    public function getType()
    {
        return 'scl';
    }

    /**
     * @return void
     */
    protected function parse()
    {
        if ($this->items === null) {
            $this->items = [];

            $disk = new \ZxFiles\Disk\Scl();
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
}
