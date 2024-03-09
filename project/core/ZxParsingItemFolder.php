<?php

class ZxParsingItemFolder extends ZxParsingItem
{
    /**
     * @return string
     *
     * @psalm-return 'folder'
     */
    public function getType()
    {
        return 'folder';
    }

    /**
     * @return void
     */
    protected function parse()
    {
    }

    public function getContent()
    {
        if (!$this->content) {
            $this->content = $this->parentMd5 . '/' . $this->itemName;
        }
        return $this->content;
    }
}
