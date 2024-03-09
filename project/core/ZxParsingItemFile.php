<?php

class ZxParsingItemFile extends ZxParsingItem
{
    /**
     * @return string
     *
     * @psalm-return 'file'
     */
    public function getType()
    {
        return 'file';
    }

    /**
     * @return void
     */
    protected function parse()
    {
    }
}
