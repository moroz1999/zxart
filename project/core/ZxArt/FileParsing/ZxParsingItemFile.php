<?php

namespace ZxArt\FileParsing;
use Override;

class ZxParsingItemFile extends ZxParsingItem
{
    #[Override] public function getType(): string
    {
        return 'file';
    }

    #[Override] protected function parse(): void
    {
        $this->items = [];
    }
}
