<?php

class ZxParsingItemFolder extends ZxParsingItem
{
    public function getType()
    {
        return 'folder';
    }

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
